<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\DashboardAnalytics;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Locked;

/**
 * Satu widget grafik untuk semua grafik beranda; jenis grafik dipilih lewat $chart (lihat DashboardAnalytics::charts()).
 */
class RoleChartWidget extends ChartWidget
{
    #[Locked]
    public string $chart = 'trend';

    protected static ?string $maxHeight = '260px';

    protected static ?string $pollingInterval = null;

    private const META = [
        'trend' => ['Tren Aktivitas', 'Jumlah data baru per bulan. Klik label legenda untuk menyembunyikan/menampilkan seri.', 'line'],
        'risk' => ['Sebaran Risiko Akademik', 'Status mahasiswa pada evaluasi monitoring terakhir.', 'doughnut'],
        'cpl' => ['Capaian CPL', 'Rata-rata skor per CPL. Hijau ≥ 70, kuning ≥ 55, merah di bawahnya.', 'bar'],
        'workload' => ['Sebaran Beban Dosen', 'Jumlah dosen per kategori beban kerja.', 'doughnut'],
    ];

    public function mount(): void
    {
        if ($this->chart === 'trend') {
            $this->filter ??= '6';
        }

        parent::mount();
    }

    public function getHeading(): string | Htmlable | null
    {
        return self::META[$this->chart][0] ?? 'Grafik';
    }

    public function getDescription(): string | Htmlable | null
    {
        return self::META[$this->chart][1] ?? null;
    }

    protected function getType(): string
    {
        return self::META[$this->chart][2] ?? 'bar';
    }

    protected function getFilters(): ?array
    {
        return $this->chart === 'trend'
            ? ['3' => '3 bulan', '6' => '6 bulan', '12' => '12 bulan']
            : null;
    }

    protected function getData(): array
    {
        $user = auth()->user();

        return $user ? (new DashboardAnalytics($user))->chartData($this->chart, $this->filter) : ['datasets' => [], 'labels' => []];
    }

    protected function getOptions(): array | RawJs | null
    {
        return match ($this->getType()) {
            'doughnut' => [
                'cutout' => '62%',
                'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 14]]],
                'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
            ],
            'bar' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'suggestedMax' => 100]],
            ],
            default => [
                'interaction' => ['mode' => 'index', 'intersect' => false],
                'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'padding' => 14]]],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        };
    }
}
