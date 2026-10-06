<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\MatriksKesesuaian;
use App\Models\Semester;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Str;

class DosenMatchingService
{
    public function __construct(
        private readonly DosenWorkloadService $workloads,
        private readonly AuditService $audit,
    ) {}

    public function calculate(Dosen $dosen, MataKuliah $mataKuliah, ?Semester $semester = null): MatriksKesesuaian
    {
        $rumpunScore = $this->scoreRumpun($dosen, $mataKuliah);
        $historyScore = $this->scoreHistory($dosen, $mataKuliah);
        $publicationScore = $this->scorePublications($dosen, $mataKuliah);
        $certificationScore = $this->scoreCertifications($dosen, $mataKuliah);
        $preferenceScore = $this->scorePreference($dosen, $mataKuliah, $semester);
        $overloadPenalty = $this->workloads->penaltyFor($dosen, $semester);

        $weights = config('sifak_m5.matching.weights');
        $rawScore = ($weights['rumpun'] * $rumpunScore)
            + ($weights['history'] * $historyScore)
            + ($weights['publication'] * $publicationScore)
            + ($weights['certification'] * $certificationScore)
            + ($weights['preference'] * $preferenceScore);
        $finalScore = max(0, min(100, $rawScore - $overloadPenalty));

        $breakdown = [
            'rumpun' => ['score' => $rumpunScore, 'weight' => $weights['rumpun'], 'weighted' => round($weights['rumpun'] * $rumpunScore, 2)],
            'history' => ['score' => $historyScore, 'weight' => $weights['history'], 'weighted' => round($weights['history'] * $historyScore, 2)],
            'publication' => ['score' => $publicationScore, 'weight' => $weights['publication'], 'weighted' => round($weights['publication'] * $publicationScore, 2)],
            'certification' => ['score' => $certificationScore, 'weight' => $weights['certification'], 'weighted' => round($weights['certification'] * $certificationScore, 2)],
            'preference' => ['score' => $preferenceScore, 'weight' => $weights['preference'], 'weighted' => round($weights['preference'] * $preferenceScore, 2)],
            'overload_penalty' => $overloadPenalty,
            'threshold' => config('sifak_m5.matching.threshold'),
        ];

        $matrix = MatriksKesesuaian::updateOrCreate(
            [
                'tenant_id' => $mataKuliah->tenant_id,
                'dosen_id' => $dosen->id,
                'mata_kuliah_id' => $mataKuliah->id,
            ],
            [
                'semester_id' => $semester?->id,
                'rumpun_score' => $rumpunScore,
                'history_score' => $historyScore,
                'publication_score' => $publicationScore,
                'certification_score' => $certificationScore,
                'preference_score' => $preferenceScore,
                'overload_penalty' => $overloadPenalty,
                'final_score' => round($finalScore, 2),
                'score_breakdown' => $breakdown,
                'justification' => $finalScore < config('sifak_m5.matching.threshold') ? 'Skor di bawah threshold, perlu justifikasi jika dipilih.' : null,
                'generated_at' => now(),
            ]
        );

        $this->audit->record('MATCHING_MATRIX_CALCULATED', 'M5', $matrix, [], $matrix->toArray());

        return $matrix;
    }

    private function scoreRumpun(Dosen $dosen, MataKuliah $mataKuliah): int
    {
        if ($dosen->rumpun_ilmu_id && $dosen->rumpun_ilmu_id === $mataKuliah->rumpun_ilmu_id) {
            return 100;
        }

        if ($dosen->kbk_id && $dosen->kbk?->name && Str::contains(Str::lower($mataKuliah->name), Str::lower($dosen->kbk->name))) {
            return 80;
        }

        return 40;
    }

    private function scoreHistory(Dosen $dosen, MataKuliah $mataKuliah): int
    {
        $histories = $dosen->riwayatMengajars()->where('mata_kuliah_id', $mataKuliah->id)->get();

        if ($histories->isEmpty()) {
            return min(60, (int) $dosen->teaching_load_sks * 8);
        }

        $experience = min(70, $histories->count() * 20);
        $evaluation = (float) $histories->avg('average_evaluation');

        return min(100, $experience + ($evaluation > 0 ? (int) min(30, $evaluation * 6) : 10));
    }

    private function scorePublications(Dosen $dosen, MataKuliah $mataKuliah): int
    {
        $needle = Str::lower($mataKuliah->name . ' ' . $mataKuliah->rumpunIlmu?->name);

        $matches = $dosen->publikasis()
            ->get()
            ->filter(fn ($publication) => Str::contains($needle, Str::lower((string) $publication->field))
                || collect($publication->keywords ?? [])->contains(fn ($keyword) => Str::contains($needle, Str::lower((string) $keyword))));

        return min(100, $matches->count() * 25);
    }

    private function scoreCertifications(Dosen $dosen, MataKuliah $mataKuliah): int
    {
        $needle = Str::lower($mataKuliah->name . ' ' . $mataKuliah->rumpunIlmu?->name);

        $matches = $dosen->sertifikasis()
            ->where('validation_status', 'validated')
            ->get()
            ->filter(fn ($certification) => Str::contains($needle, Str::lower((string) $certification->field)));

        return min(100, $matches->count() * 30);
    }

    private function scorePreference(Dosen $dosen, MataKuliah $mataKuliah, ?Semester $semester): int
    {
        $preference = $dosen->preferensiMks()
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->orderByDesc('preference_level')
            ->first();

        return match ((int) ($preference?->preference_level ?? 0)) {
            5 => 100,
            4 => 85,
            3 => 70,
            2 => 45,
            1 => 20,
            default => 50,
        };
    }
}
