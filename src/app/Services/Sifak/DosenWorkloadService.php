<?php

namespace App\Services\Sifak;

use App\Models\BebanDosen;
use App\Models\Dosen;
use App\Models\PendaftaranSidang;
use App\Models\RiwayatMengajar;
use App\Models\Semester;
use App\Services\Shared\Audit\AuditService;

class DosenWorkloadService
{
    public function __construct(private readonly AuditService $audit) {}

    public function calculate(Dosen $dosen, ?Semester $semester = null): BebanDosen
    {
        $teachingSks = RiwayatMengajar::query()
            ->where('dosen_id', $dosen->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->sum('sks') ?: $dosen->teaching_load_sks;

        $guidanceCount = PendaftaranSidang::query()
            ->where('pembimbing_id', $dosen->id)
            ->count() ?: $dosen->guidance_load;

        $examinerCount = PendaftaranSidang::query()
            ->where(function ($query) use ($dosen) {
                $query->where('penguji_1_id', $dosen->id)
                    ->orWhere('penguji_2_id', $dosen->id);
            })
            ->count() ?: $dosen->examiner_load;

        $score = $teachingSks + ($guidanceCount * 0.5) + ($examinerCount * 0.5);
        $status = $this->statusForScore($score);

        $workload = BebanDosen::updateOrCreate(
            [
                'tenant_id' => $dosen->tenant_id,
                'dosen_id' => $dosen->id,
                'semester_id' => $semester?->id,
            ],
            [
                'teaching_sks' => $teachingSks,
                'guidance_count' => $guidanceCount,
                'examiner_count' => $examinerCount,
                'research_load' => 0,
                'workload_score' => round($score, 2),
                'workload_status' => $status,
                'calculated_at' => now(),
            ]
        );

        $this->audit->record('WORKLOAD_RECALCULATED', 'M5', $workload, [], $workload->toArray());

        return $workload;
    }

    public function penaltyFor(Dosen $dosen, ?Semester $semester = null): int
    {
        $workload = BebanDosen::query()
            ->where('dosen_id', $dosen->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id), fn ($query) => $query->whereNull('semester_id'))
            ->first() ?? $this->calculate($dosen, $semester);

        return config('sifak_m5.workload.penalties.' . $workload->workload_status, 0);
    }

    private function statusForScore(float $score): string
    {
        if ($score >= config('sifak_m5.workload.overload_threshold')) {
            return 'overload';
        }

        if ($score >= config('sifak_m5.workload.high_threshold')) {
            return 'high';
        }

        return $score >= 8 ? 'normal' : 'low';
    }
}
