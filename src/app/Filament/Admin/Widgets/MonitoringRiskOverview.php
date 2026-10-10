<?php

namespace App\Filament\Admin\Widgets;

use App\Services\Sifak\MonitoringDashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonitoringRiskOverview extends BaseWidget
{
    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $summary = app(MonitoringDashboardService::class)->riskSummary();
        $severity = app(MonitoringDashboardService::class)->alertSummaryBySeverity();

        return [
            Stat::make('Mahasiswa Hijau', $summary['green'])
                ->description('Status aman pada evaluasi terakhir')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Mahasiswa Kuning', $summary['yellow'])
                ->description('Perlu perhatian/follow-up')
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Mahasiswa Merah', $summary['red'])
                ->description('Butuh tindakan prioritas')
                ->color('danger')
                ->icon('heroicon-o-bell-alert'),
            Stat::make('Alert Aktif', $summary['open_alerts'])
                ->description('High: ' . (int) ($severity['high'] ?? 0) . ' | Medium: ' . (int) ($severity['medium'] ?? 0))
                ->color('info')
                ->icon('heroicon-o-signal'),
        ];
    }
}
