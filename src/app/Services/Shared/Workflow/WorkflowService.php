<?php

namespace App\Services\Shared\Workflow;

use App\Models\WorkflowHistory;
use App\Services\Shared\Audit\AuditService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class WorkflowService
{
    public const TRANSITIONS = [
        'sidang' => [
            'draft' => ['diajukan'],
            'diajukan' => ['verifikasi'],
            'verifikasi' => ['terverifikasi', 'ditolak'],
            'terverifikasi' => ['plotting'],
            'plotting' => ['terjadwal'],
            'terjadwal' => ['dilaksanakan'],
            'dilaksanakan' => ['revisi', 'selesai'],
            'revisi' => ['selesai'],
        ],
        'surat' => [
            'draft' => ['diajukan'],
            'diajukan' => ['verifikasi'],
            'verifikasi' => ['approval', 'revisi_pemohon'],
            'approval' => ['disetujui', 'revisi_pemohon'],
            'disetujui' => ['nomor_surat'],
            'nomor_surat' => ['generated'],
            'generated' => ['arsip'],
        ],
        'krs' => [
            'draft' => ['diajukan'],
            'diajukan' => ['menunggu_pa'],
            'menunggu_pa' => ['disetujui', 'ditolak'],
            'ditolak' => ['draft'],
            'disetujui' => ['final'],
        ],
        'dokumen_ta' => [
            'draft' => ['in_progress'],
            'in_progress' => ['review'],
            'review' => ['revision', 'approved'],
            'revision' => ['review'],
            'approved' => ['final'],
            'final' => ['repository'],
        ],
        'tenant_provisioning' => [
            'draft' => ['provisioning'],
            'provisioning' => ['active', 'provisioning_failed'],
            'provisioning_failed' => ['retry'],
            'retry' => ['provisioning'],
        ],
    ];

    public function __construct(private readonly AuditService $audit) {}

    public function can(string $workflowType, ?string $fromState, string $toState): bool
    {
        $fromState = strtolower((string) $fromState);
        $toState = strtolower($toState);

        return in_array($toState, self::TRANSITIONS[$workflowType][$fromState] ?? [], true);
    }

    public function transition(
        string $workflowType,
        Model $resource,
        string $stateColumn,
        string $toState,
        string $action,
        ?string $note = null,
        array $metadata = []
    ): WorkflowHistory {
        $fromState = strtolower((string) $resource->{$stateColumn});
        $toState = strtolower($toState);

        if (! $this->can($workflowType, $fromState, $toState)) {
            throw new InvalidArgumentException('WORKFLOW_TRANSITION_INVALID');
        }

        $old = $resource->only([$stateColumn]);
        $resource->forceFill([$stateColumn => $toState])->save();

        $history = WorkflowHistory::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'workflow_type' => $workflowType,
            'resource_type' => $resource::class,
            'resource_id' => (string) $resource->getKey(),
            'from_state' => $fromState,
            'to_state' => $toState,
            'action' => $action,
            'actor_id' => Auth::id(),
            'note' => $note,
            'metadata' => $metadata,
        ]);

        $this->audit->record('WORKFLOW_TRANSITION', strtoupper($workflowType), $resource, $old, $resource->only([$stateColumn]));

        return $history;
    }
}
