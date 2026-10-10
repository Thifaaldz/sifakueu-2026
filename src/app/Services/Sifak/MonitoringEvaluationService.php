<?php

namespace App\Services\Sifak;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MonitoringIndicatorResult;
use App\Models\MonitoringOverride;
use App\Models\MonitoringRule;
use App\Models\MonitoringSnapshot;
use App\Models\SidangRegistration;
use App\Models\SidangRevision;
use App\Models\TugasAkhir;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitoringEvaluationService
{
    public function __construct(
        private readonly MonitoringRuleService $ruleService,
        private readonly MonitoringAlertService $alertService,
    ) {}

    public function evaluate(Mahasiswa $mahasiswa): MonitoringSnapshot
    {
        $this->ruleService->ensureDefaults($mahasiswa->tenant_id);

        $semesterId = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest('id')
            ->value('semester_id');

        return DB::transaction(function () use ($mahasiswa, $semesterId) {
            $snapshot = MonitoringSnapshot::create([
                'tenant_id' => $mahasiswa->tenant_id,
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semesterId,
                'overall_status' => 'green',
                'risk_score' => 0,
                'summary_payload' => [
                    'ipk' => $mahasiswa->ipk,
                    'semester' => $mahasiswa->semester,
                    'sks_lulus' => $mahasiswa->sks_lulus,
                ],
                'evaluated_at' => now(),
            ]);

            $results = MonitoringRule::query()
                ->where('tenant_id', $mahasiswa->tenant_id)
                ->where('active', true)
                ->orderByDesc('priority')
                ->get()
                ->map(fn (MonitoringRule $rule) => $this->evaluateRule($mahasiswa, $snapshot, $rule))
                ->filter();

            $totals = [
                'green' => $results->where('status', 'green')->count(),
                'yellow' => $results->where('status', 'yellow')->count(),
                'red' => $results->where('status', 'red')->count(),
            ];

            $overall = $this->overallStatus($totals);
            $score = (int) (($totals['yellow'] * 10) + ($totals['red'] * 25));
            $override = $this->activeOverride($mahasiswa, $overall);

            if ($override) {
                $overall = $override->override_status;
            }

            $snapshot->update([
                'overall_status' => $overall,
                'risk_score' => min($score, 100),
                'total_green' => $totals['green'],
                'total_yellow' => $totals['yellow'],
                'total_red' => $totals['red'],
                'summary_payload' => array_merge($snapshot->summary_payload ?? [], [
                    'override_id' => $override?->id,
                    'original_status' => $override?->original_status,
                ]),
            ]);

            $results->each(fn (MonitoringIndicatorResult $result) => $this->alertService->upsertFromIndicator($result));

            return $snapshot->refresh();
        });
    }

    private function evaluateRule(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): ?MonitoringIndicatorResult
    {
        return match ($rule->code) {
            'KRS_NOT_FINAL' => $this->evaluateKrs($mahasiswa, $snapshot, $rule),
            'SKS_PROGRESS_GAP' => $this->evaluateSksGap($mahasiswa, $snapshot, $rule),
            'IPK_LOW' => $this->evaluateIpk($mahasiswa, $snapshot, $rule),
            'TA_PROGRESS_LOW' => $this->evaluateTaProgress($mahasiswa, $snapshot, $rule),
            'TA_INACTIVE' => $this->evaluateTaInactivity($mahasiswa, $snapshot, $rule),
            'SEMESTER_WITHOUT_SEMPRO' => $this->evaluateMissingSidang($mahasiswa, $snapshot, $rule, 'sempro'),
            'SEMESTER_WITHOUT_SIDANG_TA' => $this->evaluateMissingSidang($mahasiswa, $snapshot, $rule, 'sidang'),
            'REVISION_OVERDUE' => $this->evaluateRevisionDeadline($mahasiswa, $snapshot, $rule),
            'CPL_PLO_GAP' => $this->evaluateCplGap($mahasiswa, $snapshot, $rule),
            default => null,
        };
    }

    private function evaluateKrs(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $krs = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest('id')
            ->first();

        $isFinal = $krs && in_array($krs->status, ['approved', 'final'], true);
        $status = $isFinal ? 'green' : ($krs ? 'yellow' : 'red');

        return $this->result($snapshot, $rule, $status, $isFinal ? 0 : 1, $isFinal ? 'KRS semester terakhir sudah final/disetujui.' : 'KRS semester terakhir belum final atau belum dibuat.', $krs);
    }

    private function evaluateSksGap(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $target = min(max((int) $mahasiswa->semester, 1) * 18, 144);
        $gap = max(0, $target - (int) $mahasiswa->sks_lulus);
        $status = $this->statusFromHighIsBad($gap, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $gap, "Target SKS semester {$mahasiswa->semester} adalah {$target}; SKS lulus {$mahasiswa->sks_lulus}; gap {$gap}.", $mahasiswa);
    }

    private function evaluateIpk(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $ipk = (float) $mahasiswa->ipk;
        $status = $ipk <= 0 ? 'not_applicable' : $this->statusFromLowIsBad($ipk, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $ipk ?: null, $ipk <= 0 ? 'Data IPK belum tersedia.' : "IPK mahasiswa saat ini {$ipk}.", $mahasiswa);
    }

    private function evaluateTaProgress(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $ta = $this->latestTugasAkhir($mahasiswa);
        $progress = $ta ? (float) $ta->progress_percent : null;

        if (! $ta || (int) $mahasiswa->semester < 7) {
            return $this->result($snapshot, $rule, 'not_applicable', $progress, 'TA belum wajib dimonitor pada semester ini.', $ta);
        }

        $status = $this->statusFromLowIsBad($progress, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $progress, "Progress TA saat ini {$progress}%.", $ta);
    }

    private function evaluateTaInactivity(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $ta = $this->latestTugasAkhir($mahasiswa);

        if (! $ta) {
            return $this->result($snapshot, $rule, (int) $mahasiswa->semester >= 8 ? 'yellow' : 'not_applicable', null, 'Data TA belum tersedia.', null);
        }

        $lastActivity = $ta->progressLogs()->latest('created_at')->value('created_at') ?? $ta->updated_at;
        $inactiveDays = $lastActivity ? now()->diffInDays($lastActivity) : null;
        $status = $inactiveDays === null ? 'yellow' : $this->statusFromHighIsBad($inactiveDays, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $inactiveDays, $inactiveDays === null ? 'Belum ada aktivitas TA tercatat.' : "Aktivitas TA terakhir {$inactiveDays} hari lalu.", $ta);
    }

    private function evaluateMissingSidang(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule, string $kind): MonitoringIndicatorResult
    {
        $query = SidangRegistration::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('type', function ($builder) use ($kind) {
                $builder->where('code', 'like', '%' . $kind . '%')
                    ->orWhere('name', 'like', '%' . $kind . '%');
            });

        $exists = $query->exists();
        $semester = (int) $mahasiswa->semester;

        if ($exists) {
            return $this->result($snapshot, $rule, 'green', $semester, $rule->name . ' sudah memiliki pendaftaran/proses.', $query->latest('id')->first());
        }

        $status = $this->statusFromHighIsBad($semester, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $semester, "{$rule->name}: mahasiswa semester {$semester} belum memiliki pendaftaran/proses.", $mahasiswa);
    }

    private function evaluateRevisionDeadline(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        $revision = SidangRevision::query()
            ->whereHas('registration', fn ($builder) => $builder->where('mahasiswa_id', $mahasiswa->id))
            ->whereNotIn('status', ['resolved', 'validated', 'closed'])
            ->whereNotNull('deadline')
            ->orderBy('deadline')
            ->first();

        $taRevision = $this->latestTugasAkhir($mahasiswa)?->revisionCycles()
            ->whereNotIn('status', ['resolved', 'closed', 'completed'])
            ->whereNotNull('deadline')
            ->orderBy('deadline')
            ->first();

        $deadline = $revision?->deadline ?? $taRevision?->deadline;

        if (! $deadline) {
            return $this->result($snapshot, $rule, 'green', null, 'Tidak ada revisi aktif yang memiliki deadline terbuka.', $revision ?? $taRevision);
        }

        $overdueDays = now()->startOfDay()->diffInDays($deadline, false) * -1;
        $status = $overdueDays > (float) $rule->threshold_value ? 'red' : ($overdueDays >= (float) $rule->warning_value ? 'yellow' : 'green');

        return $this->result($snapshot, $rule, $status, $overdueDays, $overdueDays > 0 ? "Revisi overdue {$overdueDays} hari." : 'Deadline revisi masih dalam batas perhatian.', $revision ?? $taRevision);
    }

    private function evaluateCplGap(Mahasiswa $mahasiswa, MonitoringSnapshot $snapshot, MonitoringRule $rule): MonitoringIndicatorResult
    {
        if (! Schema::hasTable('skor_cpl_mahasiswas')) {
            return $this->result($snapshot, $rule, 'not_applicable', null, 'Data CPL/PLO belum tersedia.', null);
        }

        $lowestScore = DB::table('skor_cpl_mahasiswas')
            ->where('tenant_id', $mahasiswa->tenant_id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->min('score');

        if ($lowestScore === null) {
            return $this->result($snapshot, $rule, 'not_applicable', null, 'Skor CPL/PLO mahasiswa belum tersedia.', null);
        }

        $gap = max(0, 80 - (float) $lowestScore);
        $status = $this->statusFromHighIsBad($gap, (float) $rule->warning_value, (float) $rule->threshold_value);

        return $this->result($snapshot, $rule, $status, $gap, "Gap CPL/PLO terbesar {$gap} dari skor minimum {$lowestScore}.", $mahasiswa);
    }

    private function latestTugasAkhir(Mahasiswa $mahasiswa): ?TugasAkhir
    {
        return TugasAkhir::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest('id')
            ->first();
    }

    private function result(MonitoringSnapshot $snapshot, MonitoringRule $rule, string $status, float|int|null $value, string $explanation, mixed $source): MonitoringIndicatorResult
    {
        return MonitoringIndicatorResult::create([
            'tenant_id' => $snapshot->tenant_id,
            'snapshot_id' => $snapshot->id,
            'rule_id' => $rule->id,
            'indicator' => $rule->metric_key,
            'metric_value' => $value,
            'status' => $status,
            'threshold_value' => $rule->threshold_value,
            'explanation' => $explanation,
            'source_reference_type' => is_object($source) ? $source::class : null,
            'source_reference_id' => is_object($source) ? $source->id : null,
            'payload' => [
                'rule_code' => $rule->code,
                'domain' => $rule->domain,
                'source_module' => $rule->source_module,
                'warning_value' => $rule->warning_value,
            ],
        ]);
    }

    private function statusFromHighIsBad(float $value, float $warningValue, float $criticalValue): string
    {
        return match (true) {
            $value > $criticalValue => 'red',
            $value > $warningValue => 'yellow',
            default => 'green',
        };
    }

    private function statusFromLowIsBad(float $value, float $warningValue, float $criticalValue): string
    {
        return match (true) {
            $value < $criticalValue => 'red',
            $value < $warningValue => 'yellow',
            default => 'green',
        };
    }

    private function overallStatus(array $totals): string
    {
        return match (true) {
            $totals['red'] > 0 => 'red',
            $totals['yellow'] > 0 => 'yellow',
            default => 'green',
        };
    }

    private function activeOverride(Mahasiswa $mahasiswa, string $originalStatus): ?MonitoringOverride
    {
        return MonitoringOverride::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('original_status', $originalStatus)
            ->where(function ($builder) {
                $builder->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->latest('id')
            ->first();
    }
}
