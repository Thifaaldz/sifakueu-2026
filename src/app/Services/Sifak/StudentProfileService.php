<?php

namespace App\Services\Sifak;

use App\Models\CompetencyGap;
use App\Models\Dosen;
use App\Models\GraduateProfile;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MahasiswaCplScore;
use App\Models\MahasiswaGraduateProfileScore;
use App\Models\MahasiswaPloScore;
use App\Models\MahasiswaProfile;
use App\Models\MataKuliah;
use App\Models\PemetaanCplPlo;
use App\Models\PemetaanMkCpl;
use App\Models\RecommendationHistory;
use App\Models\StudentRecommendation;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentProfileService
{
    private const MODEL_VERSION = 'M6-RULE-V1';

    public function __construct(private readonly AuditService $audit) {}

    public function recalculate(Mahasiswa $mahasiswa): MahasiswaProfile
    {
        return DB::transaction(function () use ($mahasiswa) {
            $this->calculateCplScores($mahasiswa);
            $this->calculatePloScores($mahasiswa);
            $this->calculateGraduateProfileScores($mahasiswa);
            $this->calculateCompetencyGaps($mahasiswa);
            $this->generateRecommendations($mahasiswa);

            $cplAverage = (float) MahasiswaCplScore::query()->where('mahasiswa_id', $mahasiswa->id)->avg('score');
            $ploAverage = (float) MahasiswaPloScore::query()->where('mahasiswa_id', $mahasiswa->id)->avg('score');
            $academicScore = max((float) $mahasiswa->ipk / 4 * 100, $this->courseAverage($mahasiswa));
            $topCpl = MahasiswaCplScore::query()->where('mahasiswa_id', $mahasiswa->id)->with('cpl')->orderByDesc('score')->limit(3)->get();
            $lowGaps = CompetencyGap::query()->where('mahasiswa_id', $mahasiswa->id)->orderByDesc('gap_score')->limit(3)->get();

            $profile = MahasiswaProfile::updateOrCreate(
                ['tenant_id' => $mahasiswa->tenant_id, 'mahasiswa_id' => $mahasiswa->id],
                [
                    'academic_score' => round(min($academicScore, 100), 2),
                    'competency_score' => round($ploAverage ?: $cplAverage ?: 0, 2),
                    'profile_status' => 'calculated',
                    'strengths' => $topCpl->map(fn (MahasiswaCplScore $score) => [
                        'code' => $score->cpl?->code,
                        'name' => $score->cpl?->name ?? $score->cpl?->description,
                        'score' => (float) $score->score,
                    ])->values()->all(),
                    'gaps' => $lowGaps->map(fn (CompetencyGap $gap) => [
                        'type' => $gap->competency_type,
                        'reference_id' => $gap->competency_reference_id,
                        'gap' => (float) $gap->gap_score,
                        'severity' => $gap->severity,
                    ])->values()->all(),
                    'summary_payload' => [
                        'ipk' => $mahasiswa->ipk,
                        'sks_lulus' => $mahasiswa->sks_lulus,
                        'cpl_average' => round($cplAverage, 2),
                        'plo_average' => round($ploAverage, 2),
                        'recommendation_count' => StudentRecommendation::query()->where('mahasiswa_id', $mahasiswa->id)->where('status', 'generated')->count(),
                    ],
                    'last_recalculated_at' => now(),
                ]
            );

            $this->audit->record('STUDENT_PROFILE_RECALCULATED', 'M6', $profile, [], $profile->toArray());

            return $profile->fresh();
        });
    }

    private function calculateCplScores(Mahasiswa $mahasiswa): void
    {
        $details = KrsDetail::query()
            ->with('mataKuliah')
            ->whereHas('krs', fn ($query) => $query->where('mahasiswa_id', $mahasiswa->id)->whereIn('status', ['approved', 'final']))
            ->whereNotNull('final_score')
            ->get();

        $grouped = collect();

        foreach ($details as $detail) {
            PemetaanMkCpl::query()
                ->where('mata_kuliah_id', $detail->mata_kuliah_id)
                ->with('cpl')
                ->get()
                ->each(function (PemetaanMkCpl $mapping) use ($grouped, $detail) {
                    $cplId = $mapping->cpl_id;
                    $items = $grouped->get($cplId, collect());
                    $items->push([
                        'score' => (float) $detail->final_score,
                        'weight' => max((float) $mapping->weight, 0.01),
                        'mata_kuliah' => $detail->mataKuliah?->code,
                    ]);
                    $grouped->put($cplId, $items);
                });
        }

        $grouped->each(function (Collection $items, int $cplId) use ($mahasiswa) {
            $totalWeight = $items->sum('weight');
            $score = $totalWeight > 0
                ? $items->sum(fn (array $item) => $item['score'] * $item['weight']) / $totalWeight
                : 0;

            MahasiswaCplScore::updateOrCreate(
                [
                    'tenant_id' => $mahasiswa->tenant_id,
                    'mahasiswa_id' => $mahasiswa->id,
                    'cpl_id' => $cplId,
                    'semester' => (string) $mahasiswa->semester,
                ],
                [
                    'score' => round($score, 2),
                    'source' => 'M6',
                    'calculated_at' => now(),
                    'payload' => ['items' => $items->values()->all()],
                ]
            );
        });
    }

    private function calculatePloScores(Mahasiswa $mahasiswa): void
    {
        $cplScores = MahasiswaCplScore::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->keyBy('cpl_id');

        $scores = collect();

        PemetaanCplPlo::query()
            ->with('plo')
            ->get()
            ->groupBy('plo_id')
            ->each(function (Collection $mappings, int $ploId) use ($mahasiswa, $cplScores, $scores) {
                $weighted = 0;
                $totalWeight = 0;
                $breakdown = [];

                foreach ($mappings as $mapping) {
                    $cplScore = $cplScores->get($mapping->cpl_id);

                    if (! $cplScore) {
                        continue;
                    }

                    $weight = max((float) $mapping->weight, 0.01);
                    $weighted += (float) $cplScore->score * $weight;
                    $totalWeight += $weight;
                    $breakdown[] = ['cpl_id' => $mapping->cpl_id, 'score' => (float) $cplScore->score, 'weight' => $weight];
                }

                if ($totalWeight <= 0) {
                    return;
                }

                $scores->push([
                    'plo_id' => $ploId,
                    'score' => round($weighted / $totalWeight, 2),
                    'payload' => ['breakdown' => $breakdown],
                ]);
            });

        $scores->sortByDesc('score')->values()->each(function (array $score, int $index) use ($mahasiswa) {
            MahasiswaPloScore::updateOrCreate(
                ['tenant_id' => $mahasiswa->tenant_id, 'mahasiswa_id' => $mahasiswa->id, 'plo_id' => $score['plo_id']],
                ['score' => $score['score'], 'rank_order' => $index + 1, 'calculated_at' => now(), 'payload' => $score['payload']]
            );
        });
    }

    private function calculateGraduateProfileScores(Mahasiswa $mahasiswa): void
    {
        $ploScores = MahasiswaPloScore::query()->where('mahasiswa_id', $mahasiswa->id)->get()->keyBy('plo_id');
        $items = collect();

        GraduateProfile::query()
            ->where(function ($query) use ($mahasiswa) {
                $query->whereNull('program_studi_id')->orWhere('program_studi_id', $mahasiswa->program_studi_id);
            })
            ->where('active', true)
            ->with('plos')
            ->get()
            ->each(function (GraduateProfile $profile) use ($items, $ploScores) {
                $weighted = 0;
                $totalWeight = 0;
                $breakdown = [];

                foreach ($profile->plos as $plo) {
                    $score = $ploScores->get($plo->id);

                    if (! $score) {
                        continue;
                    }

                    $weight = max((float) $plo->pivot->weight, 0.01);
                    $weighted += (float) $score->score * $weight;
                    $totalWeight += $weight;
                    $breakdown[] = ['plo_id' => $plo->id, 'score' => (float) $score->score, 'weight' => $weight];
                }

                if ($totalWeight > 0) {
                    $items->push(['profile' => $profile, 'score' => round($weighted / $totalWeight, 2), 'breakdown' => $breakdown]);
                }
            });

        $items->sortByDesc('score')->values()->each(function (array $item, int $index) use ($mahasiswa) {
            MahasiswaGraduateProfileScore::updateOrCreate(
                ['tenant_id' => $mahasiswa->tenant_id, 'mahasiswa_id' => $mahasiswa->id, 'graduate_profile_id' => $item['profile']->id],
                [
                    'score' => $item['score'],
                    'rank_order' => $index + 1,
                    'generated_at' => now(),
                    'payload' => ['breakdown' => $item['breakdown']],
                ]
            );
        });
    }

    private function calculateCompetencyGaps(Mahasiswa $mahasiswa): void
    {
        $target = 80;

        MahasiswaCplScore::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->each(fn (MahasiswaCplScore $score) => $this->upsertGap($mahasiswa, 'CPL', $score->cpl_id, (float) $score->score, $target));

        MahasiswaPloScore::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->each(fn (MahasiswaPloScore $score) => $this->upsertGap($mahasiswa, 'PLO', $score->plo_id, (float) $score->score, $target));
    }

    private function generateRecommendations(Mahasiswa $mahasiswa): void
    {
        StudentRecommendation::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'generated')
            ->update(['status' => 'superseded']);

        $recommendations = collect()
            ->merge($this->courseRecommendations($mahasiswa))
            ->merge($this->graduateProfileRecommendations($mahasiswa))
            ->merge($this->careerRecommendations($mahasiswa))
            ->merge($this->thesisTopicRecommendations($mahasiswa))
            ->merge($this->supervisorRecommendations($mahasiswa));

        $recommendations
            ->groupBy('recommendation_type')
            ->each(function (Collection $items, string $type) use ($mahasiswa) {
                $payload = [];

                $items->sortByDesc('score')->values()->take(5)->each(function (array $item, int $index) use ($mahasiswa, &$payload) {
                    $recommendation = StudentRecommendation::create($item + [
                        'tenant_id' => $mahasiswa->tenant_id,
                        'mahasiswa_id' => $mahasiswa->id,
                        'rank_order' => $index + 1,
                        'status' => 'generated',
                        'generated_at' => now(),
                    ]);

                    $payload[] = $recommendation->toArray();
                });

                RecommendationHistory::create([
                    'tenant_id' => $mahasiswa->tenant_id,
                    'mahasiswa_id' => $mahasiswa->id,
                    'recommendation_type' => $type,
                    'payload_json' => $payload,
                    'model_version' => self::MODEL_VERSION,
                    'generated_at' => now(),
                ]);
            });
    }

    private function courseRecommendations(Mahasiswa $mahasiswa): Collection
    {
        $takenCourseIds = KrsDetail::query()
            ->whereHas('krs', fn ($query) => $query->where('mahasiswa_id', $mahasiswa->id))
            ->where(function ($query) {
                $query->where('validation_status', 'valid')->orWhereNotNull('final_score');
            })
            ->pluck('mata_kuliah_id')
            ->all();

        return CompetencyGap::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('competency_type', 'CPL')
            ->where('gap_score', '>', 0)
            ->orderByDesc('gap_score')
            ->get()
            ->flatMap(function (CompetencyGap $gap) use ($takenCourseIds) {
                return PemetaanMkCpl::query()
                    ->where('cpl_id', $gap->competency_reference_id)
                    ->whereNotIn('mata_kuliah_id', $takenCourseIds)
                    ->with('mataKuliah')
                    ->get()
                    ->map(fn (PemetaanMkCpl $mapping) => [
                        'recommendation_type' => 'COURSE',
                        'reference_type' => MataKuliah::class,
                        'reference_id' => $mapping->mata_kuliah_id,
                        'score' => min(100, (float) $gap->gap_score * 3 + ((float) $mapping->weight * 10)),
                        'reason_summary' => 'Mata kuliah ' . ($mapping->mataKuliah?->name ?? '-') . ' relevan untuk menutup gap CPL.',
                        'payload' => ['gap_id' => $gap->id, 'cpl_id' => $gap->competency_reference_id, 'weight' => (float) $mapping->weight],
                    ]);
            });
    }

    private function graduateProfileRecommendations(Mahasiswa $mahasiswa): Collection
    {
        return MahasiswaGraduateProfileScore::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->with('graduateProfile')
            ->orderBy('rank_order')
            ->limit(5)
            ->get()
            ->map(fn (MahasiswaGraduateProfileScore $score) => [
                'recommendation_type' => 'GRADUATE_PROFILE',
                'reference_type' => GraduateProfile::class,
                'reference_id' => $score->graduate_profile_id,
                'score' => (float) $score->score,
                'reason_summary' => 'Profil lulusan cocok berdasarkan PLO dominan dan capaian CPL.',
                'payload' => ['profile_name' => $score->graduateProfile?->name, 'breakdown' => $score->payload['breakdown'] ?? []],
            ]);
    }

    private function careerRecommendations(Mahasiswa $mahasiswa): Collection
    {
        return MahasiswaGraduateProfileScore::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->with('graduateProfile')
            ->orderBy('rank_order')
            ->limit(3)
            ->get()
            ->map(fn (MahasiswaGraduateProfileScore $score) => [
                'recommendation_type' => 'CAREER',
                'reference_type' => GraduateProfile::class,
                'reference_id' => $score->graduate_profile_id,
                'score' => (float) $score->score,
                'reason_summary' => 'Arah karier ' . ($score->graduateProfile?->name ?? 'profil lulusan') . ' sesuai capaian PLO saat ini.',
                'payload' => ['career_area' => $score->graduateProfile?->name],
            ]);
    }

    private function thesisTopicRecommendations(Mahasiswa $mahasiswa): Collection
    {
        $interests = $mahasiswa->interests()->orderByRaw("FIELD(level, 'high', 'medium', 'low')")->pluck('interest_area')->all();
        $topCpl = $mahasiswa->cplScores()->with('cpl')->orderByDesc('score')->limit(3)->get()->map(fn ($score) => $score->cpl?->name ?? $score->cpl?->code)->filter()->all();
        $topics = array_values(array_unique(array_merge($interests, $topCpl, ['Software Engineering', 'Data Analytics', 'Academic Information System'])));

        return collect($topics)->take(5)->values()->map(fn (string $topic, int $index) => [
            'recommendation_type' => 'THESIS_TOPIC',
            'reference_type' => null,
            'reference_id' => null,
            'score' => 90 - ($index * 5),
            'reason_summary' => 'Topik sesuai minat dan kekuatan kompetensi mahasiswa.',
            'payload' => ['topic_area' => $topic],
        ]);
    }

    private function supervisorRecommendations(Mahasiswa $mahasiswa): Collection
    {
        $keywords = collect($mahasiswa->interests()->pluck('interest_area')->all())
            ->merge($mahasiswa->tugasAkhirs()->pluck('topik')->filter()->all())
            ->flatMap(fn ($item) => str($item)->lower()->explode(' '))
            ->filter(fn ($item) => strlen($item) >= 4)
            ->unique()
            ->values();

        return Dosen::query()
            ->where('status', 'active')
            ->with(['profil', 'bebanDosens'])
            ->get()
            ->map(function (Dosen $dosen) use ($keywords) {
                $haystack = str(implode(' ', array_filter([
                    $dosen->name,
                    $dosen->academic_position,
                    implode(' ', $dosen->skills ?? []),
                    $dosen->profil?->expertise_focus,
                    $dosen->profil?->profile_summary,
                ])))->lower();

                $matchCount = $keywords->filter(fn ($keyword) => $haystack->contains($keyword))->count();
                $loadPenalty = min((int) $dosen->guidance_load * 2, 20);
                $score = max(0, min(100, 60 + ($matchCount * 10) - $loadPenalty));

                return [
                    'recommendation_type' => 'SUPERVISOR',
                    'reference_type' => Dosen::class,
                    'reference_id' => $dosen->id,
                    'score' => $score,
                    'reason_summary' => $matchCount > 0 ? 'Keahlian dosen selaras dengan minat/topik mahasiswa.' : 'Kandidat pembimbing aktif tersedia untuk review Kaprodi/Admin.',
                    'payload' => ['matched_keywords' => $matchCount, 'guidance_load' => $dosen->guidance_load],
                ];
            });
    }

    private function upsertGap(Mahasiswa $mahasiswa, string $type, int $referenceId, float $currentScore, float $target): void
    {
        $gap = max(0, $target - $currentScore);

        CompetencyGap::updateOrCreate(
            [
                'tenant_id' => $mahasiswa->tenant_id,
                'mahasiswa_id' => $mahasiswa->id,
                'competency_type' => $type,
                'competency_reference_id' => $referenceId,
            ],
            [
                'current_score' => $currentScore,
                'target_score' => $target,
                'gap_score' => $gap,
                'severity' => match (true) {
                    $gap >= 20 => 'high',
                    $gap >= 10 => 'medium',
                    default => 'low',
                },
            ]
        );
    }

    private function courseAverage(Mahasiswa $mahasiswa): float
    {
        return (float) KrsDetail::query()
            ->whereHas('krs', fn ($query) => $query->where('mahasiswa_id', $mahasiswa->id))
            ->whereNotNull('final_score')
            ->avg('final_score');
    }
}
