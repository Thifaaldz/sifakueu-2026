<?php

namespace App\Filament\Pages;

use App\Filament\Admin\Widgets\MonitoringRiskOverview;
use App\Support\Access\RoleFeatureRegistry;
use App\Support\Dashboard\DashboardAnalytics;
use App\Support\Dashboard\DashboardData;
use App\Support\Tenancy\TenantContext;
use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Beranda per role: pekerjaan tertunda, status pribadi, alur kerja, KPI, grafik, agenda, pintasan, dan fitur milik role.
 */
class SifakDashboard extends Dashboard
{
    protected static string $view = 'filament.pages.sifak-dashboard';

    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $title = 'Beranda';

    /** Isi panel geser "data per tahap" (diisi oleh openStage). */
    public ?array $stage = null;

    public function openStage(string $pipeline, string $stage): void
    {
        $this->stage = (new DashboardAnalytics(auth()->user()))->stageRecords($pipeline, $stage)
            ?? ['title' => 'Data tidak tersedia', 'stage' => '', 'tone' => 'gray', 'url' => null, 'total' => 0, 'items' => []];
    }

    public function getTitle(): string | Htmlable
    {
        return 'Beranda';
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getWidgets(): array
    {
        $user = auth()->user();

        return $user && (new DashboardData($user))->showsRiskOverview()
            ? [MonitoringRiskOverview::class]
            : [];
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $data = new DashboardData($user);
        $analytics = new DashboardAnalytics($user);
        $tasks = collect($data->tasks())
            ->sortBy(fn (array $task) => [$task['count'] > 0 ? 0 : 1, ['danger' => 0, 'warning' => 1][$task['tone']] ?? 2])
            ->values()
            ->all();
        $actions = $data->quickActions();
        $pipelines = $analytics->pipelines();
        $roleKeys = $user->getRoleNames()->all();

        return [
            'user' => $user,
            'greeting' => $data->greeting(),
            'roles' => $data->roleLabels(),
            'tenant' => app(TenantContext::class)->get()?->name,
            'today' => now()->translatedFormat('l, d F Y'),
            'tasks' => $tasks,
            'pendingTotal' => collect($tasks)->sum('count'),
            'student' => $data->studentStatus(),
            'agenda' => $data->agenda(),
            'showAgenda' => $data->hasFeature(\App\Filament\Admin\Resources\SidangScheduleResource::class),
            'actions' => $actions,
            'roleFilters' => collect($roleKeys)
                ->filter(fn (string $role) => collect([...$tasks, ...$actions, ...$pipelines])->contains(fn (array $item) => in_array($role, $item['roles'], true)))
                ->mapWithKeys(fn (string $role) => [$role => RoleFeatureRegistry::ROLE_LABELS[$role] ?? $role])
                ->all(),
            'journey' => $analytics->studentJourney(),
            'kpis' => $analytics->kpis(),
            'pipelines' => $pipelines,
            'charts' => $analytics->charts(),
            'guides' => $analytics->guides(),
            'refreshedAt' => now()->format('H:i'),
            'features' => RoleFeatureRegistry::visibleFor($user),
            'groupIcons' => RoleFeatureRegistry::GROUP_ICONS,
        ];
    }
}
