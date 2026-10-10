<?php

namespace App\Services\Sifak;

use App\Models\GeneratedLetter;
use App\Models\JenisSurat;
use App\Models\LetterArchive;
use App\Models\LetterDistribution;
use App\Models\LetterNumber;
use App\Models\LetterNumberSequence;
use App\Models\LetterTemplate;
use App\Models\LetterVerification;
use App\Models\LetterVerificationToken;
use App\Models\Surat;
use App\Models\SuratApproval;
use App\Models\User;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LetterWorkflowService
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_UNDER_VERIFICATION = 'UNDER_VERIFICATION';
    public const STATUS_REVISION_REQUIRED = 'REVISION_REQUIRED';
    public const STATUS_VERIFIED = 'VERIFIED';
    public const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_NUMBERED = 'NUMBERED';
    public const STATUS_GENERATING = 'GENERATING';
    public const STATUS_GENERATED = 'GENERATED';
    public const STATUS_DISTRIBUTED = 'DISTRIBUTED';
    public const STATUS_ARCHIVED = 'ARCHIVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function __construct(private readonly AuditService $audit) {}

    public function submit(Surat $surat, ?User $actor = null): Surat
    {
        $jenisSurat = $surat->jenisSurat()->with('formFields')->first();

        if (! $jenisSurat?->is_active) {
            throw ValidationException::withMessages(['surat' => 'Jenis surat tidak aktif.']);
        }

        $this->ensureRequiredFields($surat, $jenisSurat);
        $this->ensureRequiredAttachments($surat, $jenisSurat);

        return DB::transaction(function () use ($surat, $jenisSurat, $actor) {
            $surat->update([
                'status' => self::STATUS_UNDER_VERIFICATION,
                'requester_type' => $surat->requester_type ?: $jenisSurat->requester_type,
                'request_number' => $surat->request_number ?: $this->makeRequestNumber($surat),
                'submitted_at' => $surat->submitted_at ?: now(),
            ]);

            $this->audit->record('LETTER_SUBMITTED', 'M2', $surat, [], $surat->toArray(), $actor?->id);

            return $surat->fresh();
        });
    }

    public function verify(Surat $surat, User $actor, ?string $note = null): Surat
    {
        return DB::transaction(function () use ($surat, $actor, $note) {
            LetterVerification::create([
                'tenant_id' => $surat->tenant_id,
                'surat_id' => $surat->id,
                'verifier_id' => $actor->id,
                'status' => 'VERIFIED',
                'note' => $note,
                'verified_at' => now(),
            ]);

            $this->ensureApprovalRows($surat);

            $hasApproval = $surat->approvals()->exists();
            $surat->update([
                'status' => $hasApproval ? self::STATUS_WAITING_APPROVAL : self::STATUS_APPROVED,
                'verified_at' => now(),
                'approved_at' => $hasApproval ? null : now(),
            ]);

            $this->audit->record('LETTER_VERIFIED', 'M2', $surat, [], ['note' => $note], $actor->id);

            return $surat->fresh();
        });
    }

    public function requestRevision(Surat $surat, User $actor, string $note): Surat
    {
        if (blank($note)) {
            throw ValidationException::withMessages(['surat' => 'Catatan revisi wajib diisi.']);
        }

        return DB::transaction(function () use ($surat, $actor, $note) {
            LetterVerification::create([
                'tenant_id' => $surat->tenant_id,
                'surat_id' => $surat->id,
                'verifier_id' => $actor->id,
                'status' => 'REVISION_REQUIRED',
                'note' => $note,
                'verified_at' => now(),
            ]);

            $surat->update(['status' => self::STATUS_REVISION_REQUIRED, 'rejection_reason' => $note]);
            $this->audit->record('LETTER_REVISION_REQUESTED', 'M2', $surat, [], ['note' => $note], $actor->id);

            return $surat->fresh();
        });
    }

    public function reject(Surat $surat, User $actor, string $reason): Surat
    {
        if (blank($reason)) {
            throw ValidationException::withMessages(['surat' => 'Alasan penolakan wajib diisi.']);
        }

        return DB::transaction(function () use ($surat, $actor, $reason) {
            $surat->approvals()
                ->where('status', 'PENDING')
                ->update(['status' => 'REJECTED', 'approver_id' => $actor->id, 'notes' => $reason, 'acted_at' => now(), 'rejected_at' => now()]);

            $surat->update([
                'status' => self::STATUS_REJECTED,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->audit->record('LETTER_REJECTED', 'M2', $surat, [], ['reason' => $reason], $actor->id);

            return $surat->fresh();
        });
    }

    public function approve(Surat $surat, User $actor, ?string $note = null): Surat
    {
        return DB::transaction(function () use ($surat, $actor, $note) {
            $approval = $surat->approvals()
                ->where('status', 'PENDING')
                ->orderBy('sequence')
                ->lockForUpdate()
                ->first();

            if (! $approval) {
                $surat->update(['status' => self::STATUS_APPROVED, 'approved_at' => $surat->approved_at ?: now()]);

                return $surat->fresh();
            }

            if (! $actor->hasRole($approval->role_name) && ! $actor->can('approve_letter')) {
                throw ValidationException::withMessages(['surat' => 'User tidak berwenang menyetujui step ini.']);
            }

            $approval->update([
                'approver_id' => $actor->id,
                'status' => 'APPROVED',
                'notes' => $note,
                'acted_at' => now(),
                'approved_at' => now(),
            ]);

            $hasPending = $surat->approvals()->where('status', 'PENDING')->exists();
            $surat->update([
                'status' => $hasPending ? self::STATUS_WAITING_APPROVAL : self::STATUS_APPROVED,
                'approved_at' => $hasPending ? $surat->approved_at : now(),
            ]);

            $this->audit->record('LETTER_APPROVED', 'M2', $surat, [], ['note' => $note, 'approval_id' => $approval->id], $actor->id);

            return $surat->fresh();
        });
    }

    public function generateNumber(Surat $surat, User $actor): LetterNumber
    {
        if (! in_array($surat->status, [self::STATUS_APPROVED, self::STATUS_NUMBERED, self::STATUS_GENERATING, self::STATUS_GENERATED], true)) {
            throw ValidationException::withMessages(['surat' => 'Nomor surat hanya bisa dibuat setelah approval final.']);
        }

        return DB::transaction(function () use ($surat, $actor) {
            $existing = LetterNumber::query()->where('surat_id', $surat->id)->first();

            if ($existing) {
                return $existing;
            }

            $jenisSurat = $surat->jenisSurat;
            $policy = 'YEARLY';
            $month = 0;

            $sequence = LetterNumberSequence::query()
                ->where('tenant_id', $surat->tenant_id)
                ->where('jenis_surat_id', $surat->jenis_surat_id)
                ->where('year', (int) now()->format('Y'))
                ->where('reset_policy', $policy)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = LetterNumberSequence::create([
                    'tenant_id' => $surat->tenant_id,
                    'jenis_surat_id' => $surat->jenis_surat_id,
                    'year' => (int) now()->format('Y'),
                    'month' => $month,
                    'reset_policy' => $policy,
                    'current_sequence' => 0,
                ]);
            }

            $sequence->increment('current_sequence');
            $sequence->refresh();

            $formatted = $this->formatNumber($jenisSurat, $sequence->current_sequence);

            $number = LetterNumber::create([
                'tenant_id' => $surat->tenant_id,
                'surat_id' => $surat->id,
                'jenis_surat_id' => $surat->jenis_surat_id,
                'sequence_number' => $sequence->current_sequence,
                'formatted_number' => $formatted,
                'generated_at' => now(),
                'generated_by' => $actor->id,
            ]);

            $surat->update(['number' => $formatted, 'status' => self::STATUS_NUMBERED]);
            $this->audit->record('LETTER_NUMBER_GENERATED', 'M2', $surat, [], $number->toArray(), $actor->id);

            return $number;
        });
    }

    public function generateDocument(Surat $surat, User $actor): GeneratedLetter
    {
        return DB::transaction(function () use ($surat, $actor) {
            $number = LetterNumber::query()->where('surat_id', $surat->id)->first()
                ?: $this->generateNumber($surat, $actor);
            $template = $this->templateFor($surat);
            $content = $this->renderTemplate($template?->content ?: $surat->jenisSurat?->template_body ?: '{{nomor_surat}} - {{perihal}}', $surat);
            $path = 'surat/generated/' . $surat->tenant_id . '/' . $surat->id . '-' . Str::slug($surat->request_number ?: $surat->subject) . '.html';

            $surat->update(['status' => self::STATUS_GENERATING]);
            Storage::disk('local')->put($path, $content);

            $generated = GeneratedLetter::create([
                'tenant_id' => $surat->tenant_id,
                'surat_id' => $surat->id,
                'letter_number_id' => $number->id,
                'letter_template_id' => $template?->id,
                'template_version' => $template?->version,
                'html_path' => $path,
                'checksum' => hash('sha256', $content),
                'generated_at' => now(),
                'generated_by' => $actor->id,
            ]);

            $publicToken = Str::random(48);

            LetterVerificationToken::create([
                'tenant_id' => $surat->tenant_id,
                'generated_letter_id' => $generated->id,
                'public_token' => $publicToken,
                'active' => true,
            ]);

            $surat->update([
                'status' => self::STATUS_GENERATED,
                'generated_file_path' => $path,
                'completed_at' => now(),
                'qr_public_token' => $publicToken,
            ]);

            $this->audit->record('LETTER_DOCUMENT_GENERATED', 'M2', $surat, [], $generated->toArray(), $actor->id);

            return $generated;
        });
    }

    public function distribute(Surat $surat, User $actor, string $channel = 'DOWNLOAD', ?string $recipient = null): LetterDistribution
    {
        $generated = $surat->generatedLetters()->latest('generated_at')->first();

        if (! $generated) {
            $generated = $this->generateDocument($surat, $actor);
        }

        $distribution = LetterDistribution::create([
            'tenant_id' => $surat->tenant_id,
            'generated_letter_id' => $generated->id,
            'channel' => $channel,
            'recipient' => $recipient ?: $surat->requester?->email,
            'status' => 'SENT',
            'sent_at' => now(),
        ]);

        $surat->update(['status' => self::STATUS_DISTRIBUTED, 'distributed_at' => now()]);
        $this->audit->record('LETTER_DISTRIBUTED', 'M2', $surat, [], $distribution->toArray(), $actor->id);

        return $distribution;
    }

    public function archive(Surat $surat, User $actor, string $classification = 'PERMANENT'): LetterArchive
    {
        $generated = $surat->generatedLetters()->latest('generated_at')->first();

        if (! $generated) {
            $generated = $this->generateDocument($surat, $actor);
        }

        $archive = LetterArchive::firstOrCreate(
            ['tenant_id' => $surat->tenant_id, 'generated_letter_id' => $generated->id],
            [
                'archive_code' => 'ARS-' . now()->format('Ymd') . '-' . str_pad((string) $surat->id, 5, '0', STR_PAD_LEFT),
                'classification' => $classification,
                'retention_until' => $classification === '5_YEARS' ? now()->addYears(5)->toDateString() : null,
                'archived_at' => now(),
                'archived_by' => $actor->id,
            ]
        );

        $surat->update(['status' => self::STATUS_ARCHIVED, 'archived_at' => now()]);
        $this->audit->record('LETTER_ARCHIVED', 'M2', $surat, [], $archive->toArray(), $actor->id);

        return $archive;
    }

    private function ensureApprovalRows(Surat $surat): void
    {
        if ($surat->approvals()->exists()) {
            return;
        }

        $jenisSurat = $surat->jenisSurat()->with('approvalFlow.steps')->first();
        $steps = $jenisSurat?->approvalFlow?->steps;

        if ($steps?->isNotEmpty()) {
            $steps->where('approval_type', '!=', 'VERIFICATION')->each(fn ($step) => SuratApproval::create([
                'tenant_id' => $surat->tenant_id,
                'surat_id' => $surat->id,
                'approval_step_id' => $step->id,
                'role_name' => $step->role_code,
                'sequence' => $step->step_order,
                'status' => 'PENDING',
            ]));

            return;
        }

        collect($jenisSurat?->approval_flow ?: [])->values()->each(fn ($role, $index) => SuratApproval::create([
            'tenant_id' => $surat->tenant_id,
            'surat_id' => $surat->id,
            'role_name' => Str::slug((string) $role, '_'),
            'sequence' => $index + 1,
            'status' => 'PENDING',
        ]));
    }

    private function ensureRequiredFields(Surat $surat, JenisSurat $jenisSurat): void
    {
        $payload = $surat->payload ?: [];

        $missing = $jenisSurat->formFields
            ->where('active', true)
            ->where('required', true)
            ->filter(fn ($field) => blank(Arr::get($payload, $field->field_key)) && ! $surat->values()->where('field_id', $field->id)->exists())
            ->pluck('label')
            ->all();

        if ($missing !== []) {
            throw ValidationException::withMessages(['surat' => 'Field wajib belum lengkap: ' . implode(', ', $missing)]);
        }
    }

    private function ensureRequiredAttachments(Surat $surat, JenisSurat $jenisSurat): void
    {
        if (! $jenisSurat->requires_attachment) {
            return;
        }

        if (filled($surat->attachments) || $surat->letterAttachments()->exists()) {
            return;
        }

        throw ValidationException::withMessages(['surat' => 'Lampiran wajib belum tersedia.']);
    }

    private function makeRequestNumber(Surat $surat): string
    {
        return 'REQ-' . now()->format('Ymd') . '-' . str_pad((string) $surat->id, 5, '0', STR_PAD_LEFT);
    }

    private function formatNumber(JenisSurat $jenisSurat, int $sequence): string
    {
        $pattern = $jenisSurat->number_pattern ?: '{sequence}/{kode_surat}/{kode_fakultas}/{bulan_romawi}/{tahun}';

        return strtr($pattern, [
            '{sequence}' => str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            '{kode_surat}' => $jenisSurat->code,
            '{kode_fakultas}' => 'FASILKOM',
            '{bulan_romawi}' => $this->romanMonth((int) now()->format('n')),
            '{tahun}' => now()->format('Y'),
        ]);
    }

    private function romanMonth(int $month): string
    {
        return [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month] ?? 'I';
    }

    private function templateFor(Surat $surat): ?LetterTemplate
    {
        return LetterTemplate::query()
            ->where('active', true)
            ->where(function ($query) use ($surat) {
                $query->where('jenis_surat_id', $surat->jenis_surat_id)->orWhereNull('jenis_surat_id');
            })
            ->latest('version')
            ->first();
    }

    private function renderTemplate(string $template, Surat $surat): string
    {
        $payload = $surat->payload ?: [];
        $replacements = [
            'nomor_surat' => $surat->number,
            'tanggal' => now()->translatedFormat('d F Y'),
            'perihal' => $surat->subject,
            'nama_pemohon' => $surat->requester?->name,
            'jenis_surat' => $surat->jenisSurat?->name,
        ];

        foreach (array_merge($payload, $replacements) as $key => $value) {
            $template = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], (string) (is_array($value) ? implode(', ', $value) : $value), $template);
        }

        return $template;
    }
}
