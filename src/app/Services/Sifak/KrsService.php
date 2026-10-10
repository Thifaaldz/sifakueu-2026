<?php

namespace App\Services\Sifak;

use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\PenawaranMataKuliah;
use App\Models\Semester;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KrsService
{
    public function __construct(
        private readonly KrsValidationService $validator,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    public function draft(Mahasiswa $mahasiswa, Semester $semester, ?int $dosenPaId = null): Krs
    {
        $krs = Krs::firstOrCreate(
            [
                'tenant_id' => $mahasiswa->tenant_id,
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semester->id,
            ],
            [
                'tahun_akademik_id' => $semester->tahun_akademik_id,
                'dosen_pa_id' => $dosenPaId,
                'academic_year' => $semester->tahunAkademik?->code ?? (string) now()->year,
                'term' => $semester->name,
                'status' => 'draft',
            ]
        );

        $this->audit->record('KRS_CREATED', 'M4', $krs, [], $krs->toArray());

        return $krs;
    }

    public function addOffering(Krs $krs, PenawaranMataKuliah $offering): KrsDetail
    {
        $detail = KrsDetail::updateOrCreate(
            [
                'tenant_id' => $krs->tenant_id,
                'krs_id' => $krs->id,
                'mata_kuliah_id' => $offering->mata_kuliah_id,
            ],
            [
                'penawaran_mata_kuliah_id' => $offering->id,
                'sks' => $offering->mataKuliah?->sks ?? 0,
                'status' => 'selected',
                'validation_status' => 'valid',
            ]
        );

        $krs->update([
            'mata_kuliah_id' => $krs->mata_kuliah_id ?: $offering->mata_kuliah_id,
            'total_sks' => $krs->details()->sum('sks'),
        ]);

        $this->audit->record('KRS_UPDATED', 'M4', $krs, [], ['added_offering_id' => $offering->id]);

        return $detail;
    }

    public function submit(Krs $krs): Krs
    {
        $this->validator->validate($krs);

        if ($this->validator->hasBlockingErrors($krs)) {
            $reasons = $krs->validationResults()
                ->where('passed', false)
                ->where('severity', 'error')
                ->pluck('message')
                ->unique()
                ->implode(' ');

            throw ValidationException::withMessages(['krs' => trim('KRS masih memiliki error validasi. ' . $reasons)]);
        }

        $old = $krs->toArray();
        $krs->update([
            'status' => 'waiting_pa',
            'submitted_at' => now(),
            'total_sks' => $krs->details()->sum('sks'),
        ]);

        $this->audit->record('KRS_SUBMITTED', 'M4', $krs, $old, $krs->fresh()->toArray());

        if ($krs->dosenPa?->user_id) {
            $this->notifications->create($krs->dosenPa->user_id, 'KRS_SUBMITTED', 'KRS Menunggu Approval', 'KRS mahasiswa ' . $krs->mahasiswa->name . ' menunggu approval.', reference: $krs);
        }

        return $krs->fresh();
    }

    public function approve(Krs $krs, ?string $note = null): Krs
    {
        $old = $krs->toArray();
        $krs->update([
            'status' => 'approved',
            'approved_by' => $this->dosenIdForCurrentUser() ?: $krs->approved_by,
            'approved_at' => now(),
            'note' => $note,
        ]);
        $krs->details()->update(['status' => 'approved']);

        $this->audit->record('KRS_APPROVED', 'M4', $krs, $old, $krs->fresh()->toArray());
        $this->notifyStudent($krs, 'KRS_APPROVED', 'KRS Disetujui', 'KRS Anda telah disetujui oleh Dosen PA.');

        return $krs->fresh();
    }

    public function requestRevision(Krs $krs, string $note): Krs
    {
        $old = $krs->toArray();
        $krs->update(['status' => 'revision_required', 'note' => $note]);

        $this->audit->record('KRS_REVISION_REQUESTED', 'M4', $krs, $old, $krs->fresh()->toArray());
        $this->notifyStudent($krs, 'KRS_REVISION_REQUESTED', 'KRS Perlu Revisi', $note);

        return $krs->fresh();
    }

    public function finalize(Krs $krs): Krs
    {
        if ($krs->status !== 'approved') {
            throw ValidationException::withMessages(['krs' => 'Hanya KRS approved yang dapat difinalisasi.']);
        }

        $old = $krs->toArray();
        $krs->update([
            'status' => 'final',
            'finalized_at' => now(),
            'finalized_by' => Auth::id(),
        ]);

        $this->audit->record('KRS_FINALIZED', 'M4', $krs, $old, $krs->fresh()->toArray());

        return $krs->fresh();
    }

    private function notifyStudent(Krs $krs, string $type, string $title, string $message): void
    {
        if ($krs->mahasiswa?->user_id) {
            $this->notifications->create($krs->mahasiswa->user_id, $type, $title, $message, reference: $krs);
        }
    }

    private function dosenIdForCurrentUser(): ?int
    {
        return Auth::id() ? \App\Models\Dosen::query()->where('user_id', Auth::id())->value('id') : null;
    }
}
