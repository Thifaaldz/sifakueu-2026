<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\SidangExaminerSummary;
use App\Models\SidangRegistration;
use App\Models\SidangResult;
use App\Models\SidangRubric;
use App\Models\SidangScore;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SidangScoringService
{
    public function __construct(private readonly AuditService $audit) {}

    public function submitScores(SidangRegistration $registration, Dosen $examiner, array $scores, ?string $generalNote = null): SidangExaminerSummary
    {
        if (! $registration->assignments()->where('dosen_id', $examiner->id)->whereIn('role', ['PENGUJI_1', 'PENGUJI_2', 'KETUA_SIDANG'])->exists()) {
            throw ValidationException::withMessages(['examiner' => 'Dosen ini bukan penguji sidang terkait.']);
        }

        $rubrics = SidangRubric::query()->where('sidang_type_id', $registration->sidang_type_id)->where('active', true)->get();
        $weighted = 0;
        $weightTotal = max(1, (float) $rubrics->sum('weight'));

        foreach ($rubrics as $rubric) {
            $score = (float) ($scores[$rubric->id]['score'] ?? 0);

            if ($score < (float) $rubric->min_score || $score > (float) $rubric->max_score) {
                throw ValidationException::withMessages(['score' => 'Nilai ' . $rubric->name . ' harus berada dalam rentang rubrik.']);
            }

            SidangScore::updateOrCreate(
                ['tenant_id' => $registration->tenant_id, 'sidang_registration_id' => $registration->id, 'examiner_id' => $examiner->id, 'sidang_rubric_id' => $rubric->id],
                ['score' => $score, 'note' => $scores[$rubric->id]['note'] ?? null, 'submitted_at' => now()]
            );

            $weighted += $score * (float) $rubric->weight;
        }

        $total = round($weighted / $weightTotal, 2);
        $summary = SidangExaminerSummary::updateOrCreate(
            ['tenant_id' => $registration->tenant_id, 'sidang_registration_id' => $registration->id, 'examiner_id' => $examiner->id],
            ['total_score' => $total, 'recommendation' => $this->recommendation($total), 'general_note' => $generalNote, 'finalized_at' => now()]
        );

        $this->audit->record('SIDANG_EXAMINER_SCORE_SUBMITTED', 'M1', $summary, [], $summary->toArray());

        return $summary;
    }

    /**
     * Rekap nilai rubrik yang diinput satu per satu (menu Sidang Scores) menjadi ringkasan penguji.
     */
    public function summarizeSubmittedScores(SidangRegistration $registration, int $examinerId): ?SidangExaminerSummary
    {
        $scores = SidangScore::query()
            ->with('rubric')
            ->where('sidang_registration_id', $registration->id)
            ->where('examiner_id', $examinerId)
            ->whereNotNull('submitted_at')
            ->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $weightTotal = max(1, (float) $scores->sum(fn (SidangScore $score) => (float) ($score->rubric?->weight ?? 1)));
        $weighted = $scores->sum(fn (SidangScore $score) => (float) $score->score * (float) ($score->rubric?->weight ?? 1));
        $total = round($weighted / $weightTotal, 2);

        return SidangExaminerSummary::updateOrCreate(
            ['tenant_id' => $registration->tenant_id, 'sidang_registration_id' => $registration->id, 'examiner_id' => $examinerId],
            ['total_score' => $total, 'recommendation' => $this->recommendation($total), 'finalized_at' => now()]
        );
    }

    public function finalizeResult(SidangRegistration $registration, ?string $note = null): SidangResult
    {
        $summaries = $registration->examinerSummaries()->get();

        if ($summaries->isEmpty()) {
            SidangScore::query()
                ->where('sidang_registration_id', $registration->id)
                ->whereNotNull('submitted_at')
                ->distinct()
                ->pluck('examiner_id')
                ->each(fn ($examinerId) => $this->summarizeSubmittedScores($registration, (int) $examinerId));

            $summaries = $registration->examinerSummaries()->get();
        }

        if ($summaries->isEmpty()) {
            throw ValidationException::withMessages(['scores' => 'Belum ada nilai penguji untuk difinalisasi.']);
        }

        $finalScore = round((float) $summaries->avg('total_score'), 2);
        $decision = $this->decision($finalScore);
        $result = SidangResult::updateOrCreate(
            ['tenant_id' => $registration->tenant_id, 'sidang_registration_id' => $registration->id],
            [
                'final_score' => $finalScore,
                'final_grade' => $this->grade($finalScore),
                'decision' => $decision,
                'decision_note' => $note,
                'decided_by' => Auth::id(),
                'decided_at' => now(),
            ]
        );

        $registration->update(['status' => $decision === 'lulus_dengan_revisi' ? 'revision' : 'completed']);
        $this->audit->record('SIDANG_RESULT_FINALIZED', 'M1', $result, [], $result->toArray());

        return $result;
    }

    public function publish(SidangResult $result): SidangResult
    {
        $result->update(['published_at' => now()]);

        return $result->fresh();
    }

    private function recommendation(float $score): string
    {
        if ($score >= config('sifak_m1.score.pass_threshold')) {
            return 'lulus';
        }

        return $score >= config('sifak_m1.score.revision_threshold') ? 'revisi' : 'mengulang';
    }

    private function decision(float $score): string
    {
        if ($score >= config('sifak_m1.score.pass_threshold')) {
            return 'lulus';
        }

        return $score >= config('sifak_m1.score.revision_threshold') ? 'lulus_dengan_revisi' : 'mengulang';
    }

    private function grade(float $score): string
    {
        return match (true) {
            $score >= 85 => 'A',
            $score >= 75 => 'B',
            $score >= 65 => 'C',
            $score >= 50 => 'D',
            default => 'E',
        };
    }
}
