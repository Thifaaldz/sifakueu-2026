<?php

namespace App\Services\Sifak;

use App\Models\SidangRegistration;
use App\Models\SidangRequirement;
use App\Models\SidangRequirementResult;
use Illuminate\Support\Facades\Auth;

class SidangRequirementService
{
    public function __construct(private readonly TaProgressService $taProgress) {}

    public function validate(SidangRegistration $registration): array
    {
        $results = [];
        $requirements = SidangRequirement::query()
            ->where('sidang_type_id', $registration->sidang_type_id)
            ->where('active', true)
            ->orderBy('sequence')
            ->get();

        foreach ($requirements as $requirement) {
            [$status, $note, $sourceType, $sourceId] = $this->evaluate($registration, $requirement);

            $result = SidangRequirementResult::updateOrCreate(
                [
                    'tenant_id' => $registration->tenant_id,
                    'sidang_registration_id' => $registration->id,
                    'sidang_requirement_id' => $requirement->id,
                ],
                [
                    'status' => $status,
                    'note' => $note,
                    'source_reference_type' => $sourceType,
                    'source_reference_id' => $sourceId,
                    'checked_by' => Auth::id(),
                    'checked_at' => now(),
                ]
            );

            $results[] = $result;
        }

        return $results;
    }

    public function isComplete(SidangRegistration $registration): bool
    {
        $this->validate($registration);

        return $this->missingRequired($registration)->isEmpty();
    }

    public function isSubmittable(SidangRegistration $registration): bool
    {
        $this->validate($registration);

        return $this->missingRequired($registration, ignoreManual: true)->isEmpty();
    }

    private function missingRequired(SidangRegistration $registration, bool $ignoreManual = false)
    {
        return ! $registration->type
            ->requirements()
            ->where('required', true)
            ->where('active', true)
            ->when($ignoreManual, fn ($query) => $query->where('requirement_type', '!=', 'manual'))
            ->whereDoesntHave('results', fn ($query) => $query
                ->where('sidang_registration_id', $registration->id)
                ->whereIn('status', ['valid', 'waived']))
            ->get();
    }

    private function evaluate(SidangRegistration $registration, SidangRequirement $requirement): array
    {
        $mahasiswa = $registration->mahasiswa;
        $ta = $registration->tugasAkhir;
        $rule = $requirement->validation_rule ?? [];

        return match ($requirement->code) {
            'MAHASISWA_AKTIF' => [
                $mahasiswa?->status === 'active' ? 'valid' : 'invalid',
                $mahasiswa?->status === 'active' ? 'Mahasiswa aktif.' : 'Status mahasiswa belum aktif.',
                $mahasiswa ? $mahasiswa::class : null,
                $mahasiswa?->id,
            ],
            'MIN_SKS' => [
                ((int) ($mahasiswa?->sks_lulus ?? 0)) >= (int) ($rule['min_sks'] ?? 0) ? 'valid' : 'invalid',
                'SKS lulus: ' . ((int) ($mahasiswa?->sks_lulus ?? 0)) . ' / minimal ' . ((int) ($rule['min_sks'] ?? 0)) . '.',
                $mahasiswa ? $mahasiswa::class : null,
                $mahasiswa?->id,
            ],
            'TA_READY' => [
                $ta && $this->taProgress->readiness($ta)['document_ready'] ? 'valid' : 'invalid',
                $ta ? 'Readiness dokumen M7 diperiksa.' : 'Tugas akhir belum terhubung.',
                $ta ? $ta::class : null,
                $ta?->id,
            ],
            'TA_FINAL_DOCUMENT' => [
                $ta && $this->taProgress->readiness($ta)['final_document_available'] ? 'valid' : 'invalid',
                $ta ? 'Dokumen final M7 diperiksa.' : 'Tugas akhir belum terhubung.',
                $ta ? $ta::class : null,
                $ta?->id,
            ],
            'PEMBIMBING_VALID' => [
                $ta?->pembimbing_1_id ? 'valid' : 'invalid',
                $ta?->pembimbing_1_id ? 'Pembimbing utama tersedia.' : 'Pembimbing utama belum ditetapkan.',
                $ta ? $ta::class : null,
                $ta?->id,
            ],
            default => [
                $requirement->requirement_type === 'manual' ? 'pending' : 'valid',
                $requirement->requirement_type === 'manual' ? 'Menunggu verifikasi manual.' : 'Validasi otomatis default.',
                null,
                null,
            ],
        };
    }
}
