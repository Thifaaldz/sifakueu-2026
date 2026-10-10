<?php

namespace App\Services\Sifak;

use App\Models\Alert;
use App\Models\MonitoringSnapshot;
use Illuminate\Support\Collection;

class MonitoringDashboardService
{
    public function riskSummary(): array
    {
        $latestSnapshotIds = MonitoringSnapshot::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('mahasiswa_id')
            ->pluck('id');

        $counts = MonitoringSnapshot::query()
            ->whereIn('id', $latestSnapshotIds)
            ->selectRaw('overall_status, COUNT(*) as total')
            ->groupBy('overall_status')
            ->pluck('total', 'overall_status');

        return [
            'green' => (int) ($counts['green'] ?? 0),
            'yellow' => (int) ($counts['yellow'] ?? 0),
            'red' => (int) ($counts['red'] ?? 0),
            'evaluated' => $latestSnapshotIds->count(),
            'open_alerts' => Alert::query()->whereIn('status', ['open', 'acknowledged', 'in_follow_up', 'escalated'])->count(),
        ];
    }

    public function alertSummaryBySeverity(): Collection
    {
        return Alert::query()
            ->whereIn('status', ['open', 'acknowledged', 'in_follow_up', 'escalated'])
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');
    }
}
