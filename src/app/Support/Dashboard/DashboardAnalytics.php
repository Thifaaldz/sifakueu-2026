<?php

namespace App\Support\Dashboard;

use App\Filament\Admin\Resources as R;
use App\Models\Cpl;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RepositoryItem;
use App\Models\SidangRegistration;
use App\Models\TugasAkhir;
use App\Models\User;
use App\Services\Sifak\MonitoringDashboardService;
use App\Support\Access\RoleFeatureRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Analitik beranda per role: alur kerja (pipeline status), KPI, grafik, perjalanan mahasiswa, dan panduan.
 * Semua angka memakai query resource (sudah ter-scope sesuai role), sama seperti DashboardData.
 */
class DashboardAnalytics
{
    private array $allowed;

    private array $roles;

    public function __construct(private readonly User $user)
    {
        $this->roles = $user->getRoleNames()->all();
        $this->allowed = RoleFeatureRegistry::allowedResources($this->roles);
    }

    /**
     * Definisi alur kerja: tahap → status. Urutan tahap = urutan proses bisnis.
     *
     * @return array<string, array{resource: class-string, title: string, hint: string, icon: string, roles: array<int, string>, stages: array<string, array{0: string, 1: array<int, string>, 2: string}>}>
     */
    public static function pipelineDefinitions(): array
    {
        return [
            'krs' => [
                'resource' => R\KrsResource::class,
                'title' => 'Alur KRS',
                'hint' => 'Mahasiswa mengisi → Dosen PA menyetujui → Admin Prodi memfinalkan.',
                'icon' => 'heroicon-o-clipboard-document-list',
                'roles' => ['dosen_pa', 'admin_prodi', 'kaprodi', 'dekan', 'wd'],
                'stages' => [
                    'draft' => ['Draft', ['draft'], 'gray'],
                    'waiting' => ['Menunggu PA', ['waiting_pa', 'submitted'], 'warning'],
                    'revision' => ['Perlu Revisi', ['revision_required'], 'danger'],
                    'approved' => ['Disetujui', ['approved'], 'info'],
                    'final' => ['Final', ['final', 'finalized'], 'success'],
                ],
            ],
            'surat' => [
                'resource' => R\SuratResource::class,
                'title' => 'Alur Surat',
                'hint' => 'Diajukan → diverifikasi → disetujui → dinomori & dibuat → didistribusikan.',
                'icon' => 'heroicon-o-envelope',
                'roles' => ['admin_prodi', 'admin_fakultas', 'kaprodi', 'dekan', 'wd'],
                'stages' => [
                    'masuk' => ['Masuk', ['SUBMITTED', 'UNDER_VERIFICATION', 'VERIFIED'], 'warning'],
                    'revisi' => ['Perlu Revisi', ['REVISION_REQUIRED'], 'danger'],
                    'approval' => ['Menunggu Approval', ['WAITING_APPROVAL'], 'warning'],
                    'proses' => ['Penomoran & Dokumen', ['APPROVED', 'NUMBERED', 'GENERATING', 'GENERATED'], 'info'],
                    'selesai' => ['Selesai', ['DISTRIBUTED', 'ARCHIVED'], 'success'],
                ],
            ],
            'sidang' => [
                'resource' => R\SidangRegistrationResource::class,
                'title' => 'Alur Pendaftaran Sidang',
                'hint' => 'Diajukan → verifikasi syarat → plotting penguji → terjadwal → selesai.',
                'icon' => 'heroicon-o-academic-cap',
                'roles' => ['admin_prodi', 'kaprodi', 'dekan', 'wd', 'baak'],
                'stages' => [
                    'diajukan' => ['Diajukan', ['submitted', 'under_verification'], 'warning'],
                    'perbaikan' => ['Perlu Perbaikan', ['draft', 'revision_required'], 'danger'],
                    'plotting' => ['Siap Plotting', ['verified', 'ready_for_plotting'], 'info'],
                    'terjadwal' => ['Terjadwal', ['scheduled', 'revision'], 'info'],
                    'selesai' => ['Selesai', ['completed'], 'success'],
                ],
            ],
            'ta' => [
                'resource' => R\TaDocumentResource::class,
                'title' => 'Alur Bimbingan Bab TA',
                'hint' => 'Mahasiswa mengunggah bab → Anda review → setujui atau minta revisi.',
                'icon' => 'heroicon-o-document-text',
                'roles' => ['dosen_pembimbing'],
                'stages' => [
                    'draft' => ['Draft', ['draft'], 'gray'],
                    'review' => ['Menunggu Review', ['submitted', 'in_review', 'under_review'], 'warning'],
                    'revisi' => ['Revisi', ['revision_required'], 'danger'],
                    'setuju' => ['Disetujui', ['approved', 'final'], 'success'],
                ],
            ],
            'revisi_sidang' => [
                'resource' => R\SidangRevisionResource::class,
                'title' => 'Alur Revisi Sidang',
                'hint' => 'Mahasiswa mengerjakan revisi → Anda validasi.',
                'icon' => 'heroicon-o-arrow-path',
                'roles' => ['dosen_penguji'],
                'stages' => [
                    'dikerjakan' => ['Dikerjakan', ['open', 'in_progress'], 'warning'],
                    'validasi' => ['Perlu Validasi', ['submitted'], 'info'],
                    'ditolak' => ['Ditolak', ['rejected'], 'danger'],
                    'selesai' => ['Tervalidasi', ['validated', 'closed'], 'success'],
                ],
            ],
            'alert' => [
                'resource' => R\AlertResource::class,
                'title' => 'Alur Tindak Lanjut Alert',
                'hint' => 'Alert muncul → diakui → ditindaklanjuti / dieskalasi → selesai.',
                'icon' => 'heroicon-o-bell-alert',
                'roles' => ['dosen_pa', 'admin_prodi', 'kaprodi', 'dekan', 'wd', 'lpm'],
                'stages' => [
                    'baru' => ['Baru', ['open'], 'danger'],
                    'diakui' => ['Diakui', ['acknowledged'], 'warning'],
                    'followup' => ['Follow-up', ['in_follow_up', 'followed_up'], 'info'],
                    'eskalasi' => ['Eskalasi', ['escalated'], 'danger'],
                    'selesai' => ['Selesai', ['resolved', 'closed'], 'success'],
                ],
            ],
            'rekomendasi' => [
                'resource' => R\RekomendasiPengampuResource::class,
                'title' => 'Alur Rekomendasi Pengampu',
                'hint' => 'Sistem menghitung kandidat → direview → diterima / ditolak.',
                'icon' => 'heroicon-o-sparkles',
                'roles' => ['kbk', 'admin_prodi'],
                'stages' => [
                    'baru' => ['Kandidat Baru', ['generated'], 'warning'],
                    'review' => ['Direview', ['reviewed'], 'info'],
                    'diterima' => ['Diterima', ['accepted'], 'success'],
                    'ditolak' => ['Ditolak / Diganti', ['rejected', 'superseded'], 'gray'],
                ],
            ],
        ];
    }

    /**
     * Alur kerja yang relevan untuk role login beserta jumlah per tahap.
     */
    public function pipelines(): array
    {
        $result = [];

        foreach (self::pipelineDefinitions() as $key => $definition) {
            $roles = array_values(array_intersect($definition['roles'], $this->roles));

            if ($roles === [] || ! $this->can($definition['resource'])) {
                continue;
            }

            $counts = $this->statusCounts($definition['resource']);
            $stages = [];

            foreach ($definition['stages'] as $stageKey => [$label, $statuses, $tone]) {
                $stages[] = [
                    'key' => $stageKey,
                    'label' => $label,
                    'tone' => $tone,
                    'count' => (int) collect($statuses)->sum(fn (string $status) => $counts[$status] ?? 0),
                ];
            }

            $total = array_sum(array_column($stages, 'count'));

            $result[] = [
                'key' => $key,
                'title' => $definition['title'],
                'hint' => $definition['hint'],
                'icon' => $definition['icon'],
                'roles' => $roles,
                'url' => $definition['resource']::getUrl(),
                'total' => $total,
                'stages' => array_map(fn (array $stage) => $stage + [
                    'percent' => $total > 0 ? round($stage['count'] / $total * 100, 1) : 0,
                ], $stages),
            ];
        }

        return $result;
    }

    /**
     * Daftar data pada satu tahap alur (untuk panel geser di beranda).
     *
     * @return array{title: string, stage: string, tone: string, url: string, total: int, items: array<int, array{title: string, subtitle: string, status: string, tone: string, url: ?string}>}|null
     */
    public function stageRecords(string $pipeline, string $stage, int $limit = 10): ?array
    {
        $definition = self::pipelineDefinitions()[$pipeline] ?? null;
        $stageDefinition = $definition['stages'][$stage] ?? null;

        if (! $definition || ! $stageDefinition || ! array_intersect($definition['roles'], $this->roles) || ! $this->can($definition['resource'])) {
            return null;
        }

        /** @var class-string<\Filament\Resources\Resource> $resource */
        $resource = $definition['resource'];
        [$label, $statuses, $tone] = $stageDefinition;

        try {
            $query = $resource::getEloquentQuery();
            $query->whereIn($query->getModel()->qualifyColumn('status'), $statuses);
            $total = (clone $query)->count();
            $records = $query->reorder()->latest($query->getModel()->qualifyColumn('updated_at'))->limit($limit)->get();
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        return [
            'title' => $definition['title'],
            'stage' => $label,
            'tone' => $tone,
            'url' => $resource::getUrl(),
            'total' => $total,
            'items' => $records->map(fn (Model $record) => [
                'title' => $this->recordTitle($record),
                'subtitle' => $this->recordSubtitle($record),
                'status' => DashboardData::statusLabel($record->getAttribute('status')),
                'tone' => DashboardData::statusTone($record->getAttribute('status')),
                'url' => $this->recordUrl($resource, $record),
            ])->all(),
        ];
    }

    /**
     * Angka kunci (KPI) per role.
     *
     * @return array<int, array{label: string, value: string, hint: string, icon: string, tone: string, url: ?string}>
     */
    public function kpis(): array
    {
        $kpis = [];
        $staff = $this->user->hasAnyRole(['admin_fakultas', 'admin_prodi', 'kaprodi', 'dekan', 'wd', 'lpm', 'baak']);

        if ($staff && $this->can(R\MahasiswaResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $query = R\MahasiswaResource::getEloquentQuery();
                $total = (clone $query)->count();
                $active = (clone $query)->where('status', 'active')->count();

                return ['Mahasiswa', number_format($total, 0, ',', '.'), "{$active} berstatus aktif", 'heroicon-o-users', 'info', R\MahasiswaResource::getUrl()];
            });

            $kpis[] = $this->safeKpi(function () {
                $avg = (float) R\MahasiswaResource::getEloquentQuery()->where('status', 'active')->avg('ipk');

                return ['Rata-rata IPK', number_format($avg, 2, ',', '.'), 'Mahasiswa aktif', 'heroicon-o-chart-bar', $avg >= 3 ? 'success' : ($avg >= 2.5 ? 'warning' : 'danger'), null];
            });
        }

        if ($staff) {
            $kpis[] = $this->safeKpi(function () {
                $risk = app(MonitoringDashboardService::class)->riskSummary();
                $share = $risk['evaluated'] > 0 ? round($risk['red'] / $risk['evaluated'] * 100) : 0;

                return ['Risiko Tinggi', (string) $risk['red'], "{$share}% dari {$risk['evaluated']} mahasiswa terevaluasi", 'heroicon-o-exclamation-triangle', $risk['red'] > 0 ? 'danger' : 'success', $this->can(R\AlertResource::class) ? R\AlertResource::getUrl() : null];
            });
        }

        if ($this->can(R\SuratResource::class) && $this->user->hasAnyRole(['admin_prodi', 'admin_fakultas', 'kaprodi', 'dekan', 'wd'])) {
            $kpis[] = $this->safeKpi(function () {
                $query = R\SuratResource::getEloquentQuery();
                $done = (clone $query)->whereIn('status', ['DISTRIBUTED', 'ARCHIVED'])->where('updated_at', '>=', now()->startOfMonth())->count();
                $open = (clone $query)->whereNotIn('status', ['DRAFT', 'DISTRIBUTED', 'ARCHIVED', 'REJECTED'])->count();

                return ['Surat Diproses', (string) $open, "{$done} selesai bulan ini", 'heroicon-o-envelope-open', $open > 20 ? 'warning' : 'info', R\SuratResource::getUrl()];
            });
        }

        if ($this->user->hasRole('dosen_pa') && $this->can(R\MahasiswaResource::class)) {
            $kpis[] = $this->safeKpi(fn () => ['Mahasiswa Perwalian', (string) R\MahasiswaResource::getEloquentQuery()->count(), 'Mahasiswa PA Anda', 'heroicon-o-user-group', 'info', R\MahasiswaResource::getUrl()]);
        }

        if ($this->user->hasRole('dosen_pembimbing') && $this->can(R\TugasAkhirResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $query = R\TugasAkhirResource::getEloquentQuery();
                $active = (clone $query)->whereNotIn('status', ['finalized', 'archived'])->count();
                $avg = (int) round((float) (clone $query)->whereNotIn('status', ['finalized', 'archived'])->avg('progress_percent'));

                return ['Mahasiswa Bimbingan', (string) $active, "Rata-rata progres {$avg}%", 'heroicon-o-document-text', 'info', R\TugasAkhirResource::getUrl()];
            });
        }

        if ($this->user->hasRole('dosen_penguji') && $this->can(R\SidangScheduleResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $week = R\SidangScheduleResource::getEloquentQuery()
                    ->whereIn('status', ['final', 'validated', 'rescheduled', 'scheduled'])
                    ->whereBetween('tanggal', [today(), today()->addDays(7)])
                    ->count();

                return ['Menguji 7 Hari ke Depan', (string) $week, 'Jadwal sidang terdekat', 'heroicon-o-calendar-days', $week > 0 ? 'warning' : 'success', R\SidangScheduleResource::getUrl()];
            });
        }

        if ($this->user->hasAnyRole(['dosen', 'kepala_laboratorium', 'kbk']) && $this->can(R\BebanDosenResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $query = R\BebanDosenResource::getEloquentQuery();
                $over = (clone $query)->where('workload_status', 'overload')->count();
                $avg = (float) (clone $query)->avg('workload_score');

                return ['Skor Beban Dosen', number_format($avg, 1, ',', '.'), $over > 0 ? "{$over} catatan overload" : 'Tidak ada overload', 'heroicon-o-scale', $over > 0 ? 'warning' : 'success', R\BebanDosenResource::getUrl()];
            });
        }

        if ($this->user->hasRole('lpm') && $this->can(R\MahasiswaCplScoreResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $avg = (float) R\MahasiswaCplScoreResource::getEloquentQuery()->avg('score');

                return ['Rata-rata Capaian CPL', number_format($avg, 1, ',', '.'), 'Seluruh CPL & mahasiswa', 'heroicon-o-trophy', $avg >= 70 ? 'success' : 'warning', R\MahasiswaCplScoreResource::getUrl()];
            });
        }

        if ($this->user->hasRole('baak') && $this->can(R\SidangResultResource::class)) {
            $kpis[] = $this->safeKpi(function () {
                $query = R\SidangResultResource::getEloquentQuery()->whereNotNull('published_at');
                $month = (clone $query)->where('published_at', '>=', now()->startOfMonth())->count();

                return ['Hasil Sidang Terpublikasi', (string) $query->count(), "{$month} bulan ini", 'heroicon-o-trophy', 'success', R\SidangResultResource::getUrl()];
            });
        }

        return array_values(array_filter($kpis));
    }

    /**
     * Grafik yang tampil untuk role login (kunci = RoleChartWidget::$chart).
     *
     * @return array<int, string>
     */
    public function charts(): array
    {
        $charts = [];

        if ($this->trendSeries() !== []) {
            $charts[] = 'trend';
        }

        if ($this->user->hasAnyRole(['admin_fakultas', 'admin_prodi', 'kaprodi', 'dekan', 'wd', 'lpm', 'baak'])) {
            $charts[] = 'risk';
        }

        if ($this->can(R\MahasiswaCplScoreResource::class)) {
            $charts[] = 'cpl';
        }

        if ($this->user->hasAnyRole(['dekan', 'wd', 'kaprodi', 'kbk', 'kepala_laboratorium']) && $this->can(R\BebanDosenResource::class)) {
            $charts[] = 'workload';
        }

        return $charts;
    }

    /**
     * Seri tren aktivitas bulanan sesuai role.
     *
     * @return array<string, array{0: class-string, 1: string, 2: ?callable}>
     */
    public function trendSeries(): array
    {
        $candidates = [
            'mahasiswa' => [
                'Dokumen TA diunggah' => [R\TaDocumentResource::class, 'created_at', null],
                'Surat diajukan' => [R\SuratResource::class, 'created_at', null],
            ],
            'dosen_pa' => ['KRS mahasiswa PA' => [R\KrsResource::class, 'created_at', null]],
            'dosen_pembimbing' => ['Bab TA masuk' => [R\TaDocumentResource::class, 'created_at', null]],
            'dosen_penguji' => ['Nilai sidang diinput' => [R\SidangScoreResource::class, 'created_at', null]],
            'admin_prodi' => [
                'Pendaftaran sidang' => [R\SidangRegistrationResource::class, 'created_at', null],
                'Surat masuk' => [R\SuratResource::class, 'created_at', null],
                'KRS diajukan' => [R\KrsResource::class, 'created_at', null],
            ],
            'admin_fakultas' => [
                'Surat masuk' => [R\SuratResource::class, 'created_at', null],
                'Surat selesai' => [R\SuratResource::class, 'updated_at', fn (Builder $q) => $q->whereIn('status', ['DISTRIBUTED', 'ARCHIVED'])],
            ],
            'kaprodi' => [
                'Alert akademik' => [R\AlertResource::class, 'created_at', null],
                'Pendaftaran sidang' => [R\SidangRegistrationResource::class, 'created_at', null],
                'Surat masuk' => [R\SuratResource::class, 'created_at', null],
            ],
            'dekan' => [
                'Alert akademik' => [R\AlertResource::class, 'created_at', null],
                'Surat masuk' => [R\SuratResource::class, 'created_at', null],
            ],
            'wd' => [
                'Alert akademik' => [R\AlertResource::class, 'created_at', null],
                'Surat masuk' => [R\SuratResource::class, 'created_at', null],
            ],
            'lpm' => ['Alert akademik' => [R\AlertResource::class, 'created_at', null]],
            'baak' => ['Hasil sidang terpublikasi' => [R\SidangResultResource::class, 'published_at', fn (Builder $q) => $q->whereNotNull('published_at')]],
            'kbk' => ['Rekomendasi pengampu' => [R\RekomendasiPengampuResource::class, 'created_at', null]],
        ];

        $series = [];

        foreach ($this->roles as $role) {
            foreach ($candidates[$role] ?? [] as $label => $definition) {
                if ($this->can($definition[0])) {
                    $series[$label] = $definition;
                }
            }
        }

        return array_slice($series, 0, 4, true);
    }

    /**
     * Data grafik Chart.js.
     */
    public function chartData(string $chart, ?string $filter = null): array
    {
        try {
            return match ($chart) {
                'trend' => $this->trendData((int) ($filter ?: 6)),
                'risk' => $this->riskData(),
                'cpl' => $this->cplData(),
                'workload' => $this->workloadData(),
                default => ['datasets' => [], 'labels' => []],
            };
        } catch (Throwable $e) {
            report($e);

            return ['datasets' => [], 'labels' => []];
        }
    }

    /**
     * Langkah perjalanan akademik mahasiswa (KRS → TA → Sidang → Revisi → Repositori).
     *
     * @return array<int, array{label: string, detail: string, state: string, url: ?string}>|null
     */
    public function studentJourney(): ?array
    {
        if (! $this->user->hasRole('mahasiswa')) {
            return null;
        }

        $mahasiswa = Mahasiswa::query()->where('user_id', $this->user->id)->first();

        if (! $mahasiswa) {
            return null;
        }

        $krs = Krs::query()->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $ta = TugasAkhir::query()->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $sidang = SidangRegistration::query()->with('result')->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $repo = $ta ? RepositoryItem::query()->where('tugas_akhir_id', $ta->id)->latest('id')->first() : null;

        $url = fn (string $resource, string $page = 'index') => $this->can($resource) ? $resource::getUrl($page) : null;

        $krsDone = $krs && in_array($krs->status, ['approved', 'final', 'finalized'], true);
        $taReady = $ta && in_array($ta->status, ['ready_for_finalization', 'finalized', 'archived'], true);
        $sidangDone = $sidang && ($sidang->status === 'completed' || $sidang->result);
        $sidangScheduled = $sidang && in_array($sidang->status, ['scheduled', 'revision', 'completed'], true);

        $steps = [
            [
                'label' => 'KRS Semester Ini',
                'detail' => $krs ? DashboardData::statusLabel($krs->status) : 'Belum mengisi KRS',
                'done' => $krsDone,
                'url' => $url(R\KrsResource::class, $krs ? 'index' : 'create'),
            ],
            [
                'label' => 'Tugas Akhir',
                'detail' => $ta ? 'Progres ' . (int) $ta->progress_percent . '% · ' . DashboardData::statusLabel($ta->status) : 'Belum terdaftar',
                'done' => $taReady,
                'url' => $url(R\TaDocumentResource::class),
            ],
            [
                'label' => 'Daftar Sidang',
                'detail' => $sidang ? DashboardData::statusLabel($sidang->status) : 'Belum mendaftar',
                'done' => $sidang && ! in_array($sidang->status, ['draft', 'revision_required', 'submitted', 'under_verification'], true),
                'url' => $url(R\SidangRegistrationResource::class, $sidang ? 'index' : 'create'),
            ],
            [
                'label' => 'Sidang & Nilai',
                'detail' => $sidang?->result ? 'Nilai ' . $sidang->result->final_grade : ($sidangScheduled ? 'Terjadwal' : 'Menunggu jadwal'),
                'done' => $sidangDone,
                'url' => $url(R\SidangScheduleResource::class),
            ],
            [
                'label' => 'Repositori & Yudisium',
                'detail' => $repo ? DashboardData::statusLabel($repo->status) : 'Setelah TA final',
                'done' => $repo && $repo->status === 'published',
                'url' => $url(R\RepositoryItemResource::class),
            ],
        ];

        $currentFound = false;

        return array_map(function (array $step) use (&$currentFound) {
            $state = $step['done'] ? 'done' : (! $currentFound ? 'current' : 'todo');
            $currentFound = $currentFound || $state === 'current';

            return ['label' => $step['label'], 'detail' => $step['detail'], 'state' => $state, 'url' => $step['url']];
        }, $steps);
    }

    /**
     * Panduan singkat 3 langkah per role.
     *
     * @return array<int, array{role: string, steps: array<int, string>}>
     */
    public function guides(): array
    {
        $guides = [
            'mahasiswa' => [
                'Lihat "Perjalanan Akademik" untuk tahu posisi Anda saat ini dan langkah berikutnya.',
                'Kartu merah/kuning di "Perlu Tindakan" adalah hal yang harus segera Anda kerjakan.',
                'Gunakan "Akses Cepat" untuk mengisi KRS, mengajukan surat, atau mendaftar sidang.',
            ],
            'dosen' => [
                'Mulai dari "Perlu Tindakan": angka menunjukkan berapa data yang menunggu Anda.',
                'Klik tahap pada "Alur Kerja" untuk melihat daftar mahasiswa di tahap itu.',
                'Lengkapi profil & publikasi lewat "Akses Cepat" agar rekomendasi pengampu akurat.',
            ],
            'admin_prodi' => [
                'Proses kartu "Perlu Tindakan" dari yang merah (mendesak) ke biru (informasi).',
                'Pantau "Alur Kerja" untuk menemukan penumpukan di satu tahap (bottleneck).',
                'Klik angka tahap untuk membuka daftar data, lalu klik baris untuk memprosesnya.',
            ],
            'admin_fakultas' => [
                'Surat mengalir: approval TU → penomoran → generate → distribusi.',
                'Klik tahap "Penomoran & Dokumen" untuk melihat surat yang siap Anda proses.',
                'Gunakan "Akses Cepat" untuk mengelola pengguna, mahasiswa, dan dosen.',
            ],
            'pimpinan' => [
                'Angka kunci di atas merangkum kondisi fakultas/prodi saat ini.',
                'Grafik tren & sebaran risiko membantu melihat perubahan dari bulan ke bulan.',
                'Persetujuan yang menunggu Anda selalu tampil di "Perlu Tindakan".',
            ],
            'mutu' => [
                'Pantau rata-rata capaian CPL dan sebaran risiko akademik.',
                'Klik grafik CPL untuk menemukan CPL yang skornya rendah.',
                'Buka "Gap Kompetensi" untuk melihat mahasiswa yang perlu intervensi.',
            ],
        ];

        $map = [
            'mahasiswa' => 'mahasiswa',
            'dosen' => 'dosen', 'dosen_pa' => 'dosen', 'dosen_pembimbing' => 'dosen', 'dosen_penguji' => 'dosen', 'kepala_laboratorium' => 'dosen', 'kbk' => 'dosen',
            'admin_prodi' => 'admin_prodi',
            'admin_fakultas' => 'admin_fakultas', 'super_admin' => 'admin_fakultas',
            'kaprodi' => 'pimpinan', 'dekan' => 'pimpinan', 'wd' => 'pimpinan',
            'lpm' => 'mutu', 'baak' => 'pimpinan',
        ];

        $key = collect($this->roles)->map(fn (string $role) => $map[$role] ?? null)->filter()->first();

        return $key ? $guides[$key] : $guides['pimpinan'];
    }

    private function trendData(int $months): array
    {
        $months = max(3, min(12, $months));
        $start = now()->startOfMonth()->subMonths($months - 1);
        $periods = collect(range(0, $months - 1))->map(fn (int $i) => $start->copy()->addMonths($i));
        $palette = ['#0d9488', '#f59e0b', '#6366f1', '#ef4444'];
        $datasets = [];
        $i = 0;

        foreach ($this->trendSeries() as $label => [$resource, $column, $scope]) {
            $query = $resource::getEloquentQuery();
            $qualified = $query->getModel()->qualifyColumn($column);
            $query->where($qualified, '>=', $start);

            if ($scope) {
                $scope($query);
            }

            $dates = $query->reorder()->pluck($qualified);
            $byMonth = $dates->filter()->countBy(fn ($date) => Carbon::parse($date)->format('Y-m'));
            $color = $palette[$i++ % count($palette)];

            $datasets[] = [
                'label' => $label,
                'data' => $periods->map(fn (Carbon $p) => (int) ($byMonth[$p->format('Y-m')] ?? 0))->all(),
                'borderColor' => $color,
                'backgroundColor' => $color . '22',
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $periods->map(fn (Carbon $p) => $p->translatedFormat('M y'))->all(),
        ];
    }

    private function riskData(): array
    {
        $risk = app(MonitoringDashboardService::class)->riskSummary();

        return [
            'datasets' => [[
                'data' => [$risk['green'], $risk['yellow'], $risk['red']],
                'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444'],
                'borderWidth' => 0,
            ]],
            'labels' => ['Aman (hijau)', 'Perlu perhatian (kuning)', 'Prioritas (merah)'],
        ];
    }

    private function cplData(): array
    {
        $averages = R\MahasiswaCplScoreResource::getEloquentQuery()
            ->reorder()
            ->selectRaw('cpl_id, AVG(score) as avg_score')
            ->groupBy('cpl_id')
            ->toBase()
            ->get();
        $codes = Cpl::query()->whereIn('id', $averages->pluck('cpl_id'))->pluck('code', 'id');

        $rows = $averages
            ->map(fn ($row) => (object) ['code' => $codes[$row->cpl_id] ?? 'CPL ' . $row->cpl_id, 'avg_score' => $row->avg_score])
            ->sortBy('code', SORT_NATURAL)
            ->take(15)
            ->values();

        return [
            'datasets' => [[
                'label' => $this->user->hasRole('mahasiswa') ? 'Skor CPL saya' : 'Rata-rata skor',
                'data' => $rows->map(fn ($row) => round((float) $row->avg_score, 1))->all(),
                'backgroundColor' => $rows->map(fn ($row) => (float) $row->avg_score >= 70 ? '#14b8a6' : ((float) $row->avg_score >= 55 ? '#f59e0b' : '#ef4444'))->all(),
                'borderRadius' => 6,
            ]],
            'labels' => $rows->pluck('code')->all(),
        ];
    }

    private function workloadData(): array
    {
        $counts = R\BebanDosenResource::getEloquentQuery()
            ->reorder()
            ->selectRaw('workload_status as s, COUNT(*) as total')
            ->groupBy('workload_status')
            ->pluck('total', 's');

        $labels = ['low' => 'Ringan', 'normal' => 'Normal', 'high' => 'Tinggi', 'overload' => 'Berlebih'];
        $known = collect($labels)->map(fn ($label, $key) => (int) ($counts[$key] ?? 0));
        $other = $counts->except(array_keys($labels))->sum();

        return [
            'datasets' => [[
                'data' => [...$known->values()->all(), ...($other > 0 ? [(int) $other] : [])],
                'backgroundColor' => ['#38bdf8', '#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                'borderWidth' => 0,
            ]],
            'labels' => [...array_values($labels), ...($other > 0 ? ['Lainnya'] : [])],
        ];
    }

    private function statusCounts(string $resource): array
    {
        try {
            $query = $resource::getEloquentQuery();
            $column = $query->getModel()->qualifyColumn('status');

            return $query->reorder()
                ->selectRaw("{$column} as s, COUNT(*) as total")
                ->groupBy($column)
                ->pluck('total', 's')
                ->map(fn ($total) => (int) $total)
                ->all();
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    private function recordTitle(Model $record): string
    {
        foreach (['mahasiswa.name', 'registration.mahasiswa.name', 'tugasAkhir.mahasiswa.name', 'dosen.name'] as $path) {
            try {
                $value = data_get($record, $path);
            } catch (Throwable) {
                $value = null;
            }

            if (filled($value)) {
                return (string) $value;
            }
        }

        foreach (['subject', 'title', 'judul', 'name'] as $attribute) {
            if (filled($record->getAttribute($attribute))) {
                return (string) $record->getAttribute($attribute);
            }
        }

        return class_basename($record) . ' #' . $record->getKey();
    }

    private function recordSubtitle(Model $record): string
    {
        $parts = [];

        foreach (['subject', 'title', 'judul'] as $attribute) {
            $value = $record->getAttribute($attribute);
            if (filled($value) && $value !== $this->recordTitle($record)) {
                $parts[] = str($value)->limit(60)->toString();
                break;
            }
        }

        if ($record->updated_at) {
            $parts[] = 'diperbarui ' . $record->updated_at->diffForHumans();
        }

        return implode(' · ', $parts);
    }

    private function recordUrl(string $resource, Model $record): ?string
    {
        try {
            foreach (['view', 'edit'] as $page) {
                if ($resource::hasPage($page) && ($page === 'view' ? $resource::canView($record) : $resource::canEdit($record))) {
                    return $resource::getUrl($page, ['record' => $record]);
                }
            }
        } catch (Throwable) {
            // Halaman tidak tersedia di panel ini: jatuh ke daftar.
        }

        return null;
    }

    private function safeKpi(callable $callback): ?array
    {
        try {
            [$label, $value, $hint, $icon, $tone, $url] = $callback();

            return compact('label', 'value', 'hint', 'icon', 'tone', 'url');
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private function can(string $resource): bool
    {
        return in_array($resource, $this->allowed, true)
            && RoleFeatureRegistry::registeredInPanel($resource)
            && $resource::canViewAny();
    }
}
