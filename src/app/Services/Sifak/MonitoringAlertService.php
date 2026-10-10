<?php

namespace App\Services\Sifak;

use App\Models\Alert;
use App\Models\AlertEscalation;
use App\Models\AlertFollowup;
use App\Models\MonitoringIndicatorResult;

class MonitoringAlertService
{
    public function upsertFromIndicator(MonitoringIndicatorResult $indicator): ?Alert
    {
        if (! in_array($indicator->status, ['yellow', 'red'], true)) {
            return null;
        }

        $snapshot = $indicator->snapshot;
        $rule = $indicator->rule;
        $type = $rule?->code ?? $indicator->indicator;
        $severity = $indicator->status === 'red' ? 'high' : ($rule?->severity ?? 'medium');

        return Alert::updateOrCreate(
            [
                'tenant_id' => $indicator->tenant_id,
                'mahasiswa_id' => $snapshot->mahasiswa_id,
                'type' => $type,
                'status' => 'open',
            ],
            [
                'monitoring_snapshot_id' => $snapshot->id,
                'rule_id' => $indicator->rule_id,
                'indicator_result_id' => $indicator->id,
                'alert_type' => $type,
                'risk_status' => $indicator->status,
                'severity' => $severity,
                'title' => $this->title($indicator),
                'description' => $indicator->explanation,
                'payload' => [
                    'metric_value' => $indicator->metric_value,
                    'threshold_value' => $indicator->threshold_value,
                    'source_module' => $rule?->source_module,
                    'domain' => $rule?->domain,
                ],
            ]
        );
    }

    public function acknowledge(Alert $alert, ?int $userId = null): Alert
    {
        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);

        return $alert;
    }

    public function followUp(Alert $alert, string $note, string $actionType = 'follow_up', ?string $nextActionDate = null, ?int $actorId = null): AlertFollowup
    {
        $followup = AlertFollowup::create([
            'tenant_id' => $alert->tenant_id,
            'alert_id' => $alert->id,
            'actor_id' => $actorId,
            'action_type' => $actionType,
            'note' => $note,
            'next_action_date' => $nextActionDate,
            'status' => 'open',
        ]);

        $alert->update(['status' => 'in_follow_up']);

        return $followup;
    }

    public function escalate(Alert $alert, string $toRole, string $reason, ?string $fromRole = null, ?int $actorId = null): AlertEscalation
    {
        $escalation = AlertEscalation::create([
            'tenant_id' => $alert->tenant_id,
            'alert_id' => $alert->id,
            'from_role' => $fromRole,
            'to_role' => $toRole,
            'reason' => $reason,
            'escalated_by' => $actorId,
            'escalated_at' => now(),
        ]);

        $alert->update([
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);

        return $escalation;
    }

    public function resolve(Alert $alert, ?int $userId = null): Alert
    {
        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);

        return $alert;
    }

    private function title(MonitoringIndicatorResult $indicator): string
    {
        $ruleName = $indicator->rule?->name ?? str($indicator->indicator)->headline()->toString();

        return $indicator->status === 'red'
            ? 'Prioritas: ' . $ruleName
            : 'Perhatian: ' . $ruleName;
    }
}
