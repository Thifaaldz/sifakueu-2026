<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\SidangRegistration;
use App\Models\Semester;
use Illuminate\Support\Collection;

class SidangRecommendationService
{
    public function __construct(private readonly DosenWorkloadService $workload) {}

    public function examiners(SidangRegistration $registration, int $limit = 5): Collection
    {
        $ta = $registration->tugasAkhir;
        $supervisorIds = array_filter([$ta?->pembimbing_1_id, $ta?->pembimbing_2_id]);
        $semester = $registration->semester_id ? Semester::find($registration->semester_id) : null;
        $keywords = collect($ta?->keywords ?? [])->map(fn ($keyword) => strtolower((string) $keyword))->all();

        return Dosen::query()
            ->where('status', 'active')
            ->when(! config('sifak_m1.policy.allow_supervisor_as_examiner'), fn ($query) => $query->whereNotIn('id', $supervisorIds))
            ->get()
            ->map(function (Dosen $dosen) use ($keywords, $semester) {
                $skills = collect($dosen->skills ?? [])->map(fn ($skill) => strtolower((string) $skill))->all();
                $match = count(array_intersect($keywords, $skills)) * 15;
                $base = 70 + $match;
                $penalty = $this->workload->penaltyFor($dosen, $semester);

                return [
                    'dosen_id' => $dosen->id,
                    'name' => $dosen->name,
                    'score' => max(0, min(100, $base - $penalty)),
                    'reason' => $match > 0 ? 'Keahlian/keyword TA relevan.' : 'Dosen aktif dengan beban tersedia.',
                    'workload_penalty' => $penalty,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take($limit);
    }
}
