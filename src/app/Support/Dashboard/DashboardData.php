<?php

namespace App\Support\Dashboard;

use App\Filament\Admin\Resources as R;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\SidangRegistration;
use App\Models\Surat;
use App\Models\TugasAkhir;
use App\Models\User;
use App\Services\Sifak\TaProgressService;
use App\Support\Access\RoleFeatureRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Isi dashboard beranda per role. Semua angka dihitung lewat query resource (sudah ter-scope sesuai role),
 * dan hanya untuk fitur yang memang milik role tersebut.
 */
class DashboardData
{
    private array $allowed;

    public function __construct(private readonly User $user)
    {
        $this->allowed = RoleFeatureRegistry::allowedResources($user->getRoleNames()->all());
    }

    public function greeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    public function roleLabels(): array
    {
        return RoleFeatureRegistry::roleLabels($this->user->getRoleNames()->all());
    }

    /**
     * Pekerjaan yang menunggu tindakan user.
     *
     * @return array<int, array{roles: array<int, string>, label: string, hint: string, count: int, url: string, tone: string, icon: string}>
     */
    public function tasks(): array
    {
        $definitions = [
            // Mahasiswa
            'mahasiswa' => [
                [R\KrsResource::class, 'KRS perlu dilengkapi', 'KRS masih draft atau diminta revisi Dosen PA.', fn (Builder $q) => $q->whereIn('status', ['draft', 'revision_required']), 'warning', 'heroicon-o-clipboard-document-list'],
                [R\AlertResource::class, 'Peringatan akademik baru', 'Baca dan konfirmasi (acknowledge) peringatan.', fn (Builder $q) => $q->where('status', 'open'), 'danger', 'heroicon-o-bell-alert'],
                [R\SuratResource::class, 'Surat perlu diperbaiki', 'Pengajuan surat masih draft atau diminta revisi.', fn (Builder $q) => $q->whereIn('status', ['DRAFT', 'REVISION_REQUIRED']), 'warning', 'heroicon-o-envelope'],
                [R\SidangRegistrationResource::class, 'Pendaftaran sidang belum diajukan', 'Lengkapi syarat lalu klik Submit.', fn (Builder $q) => $q->whereIn('status', ['draft', 'revision_required']), 'warning', 'heroicon-o-academic-cap'],
                [R\SidangRevisionResource::class, 'Revisi sidang belum selesai', 'Kerjakan revisi sebelum deadline.', fn (Builder $q) => $q->whereIn('status', ['open', 'in_progress', 'rejected']), 'danger', 'heroicon-o-arrow-path'],
                [R\TaDocumentResource::class, 'Bab TA perlu revisi', 'Pembimbing meminta revisi; unggah versi baru.', fn (Builder $q) => $q->where('status', 'revision_required'), 'danger', 'heroicon-o-document-text'],
            ],
            // Dosen PA
            'dosen_pa' => [
                [R\KrsResource::class, 'KRS menunggu persetujuan', 'KRS mahasiswa PA yang sudah diajukan.', fn (Builder $q) => $q->where('status', 'waiting_pa'), 'warning', 'heroicon-o-clipboard-document-check'],
                [R\AlertResource::class, 'Alert mahasiswa PA', 'Alert yang belum ditindaklanjuti.', fn (Builder $q) => $q->whereIn('status', ['open', 'acknowledged', 'escalated']), 'danger', 'heroicon-o-bell-alert'],
            ],
            'dosen_pembimbing' => [
                [R\TaDocumentResource::class, 'Dokumen TA menunggu review', 'Bab yang dikirim mahasiswa bimbingan.', fn (Builder $q) => $q->whereIn('status', ['submitted', 'under_review']), 'warning', 'heroicon-o-document-magnifying-glass'],
            ],
            'dosen_penguji' => [
                [R\SidangScoreResource::class, 'Nilai sidang belum disubmit', 'Nilai rubrik yang masih bisa diubah.', fn (Builder $q) => $q->whereNull('submitted_at'), 'warning', 'heroicon-o-pencil-square'],
                [R\SidangRevisionResource::class, 'Revisi menunggu validasi', 'Revisi mahasiswa yang perlu Anda periksa.', fn (Builder $q) => $q->whereIn('status', ['open', 'in_progress', 'submitted']), 'info', 'heroicon-o-check-badge'],
                [R\SidangScheduleResource::class, 'Jadwal menguji mendatang', 'Sidang terjadwal mulai hari ini.', fn (Builder $q) => $q->whereIn('status', ['final', 'validated', 'rescheduled'])->whereDate('tanggal', '>=', today()), 'info', 'heroicon-o-calendar-days'],
            ],
            // Admin Prodi
            'admin_prodi' => [
                [R\SidangRegistrationResource::class, 'Pendaftaran sidang perlu verifikasi', 'Periksa syarat lalu Verifikasi / Minta Perbaikan.', fn (Builder $q) => $q->whereIn('status', ['submitted', 'under_verification']), 'warning', 'heroicon-o-shield-check'],
                [R\SidangRegistrationResource::class, 'Siap plotting & penjadwalan', 'Pendaftaran terverifikasi tanpa jadwal final.', fn (Builder $q) => $q->whereIn('status', ['verified', 'ready_for_plotting']), 'info', 'heroicon-o-user-group'],
                [R\SidangScheduleResource::class, 'Jadwal sidang bentrok', 'Jadwalkan ulang lalu finalisasi.', fn (Builder $q) => $q->where('status', 'conflict'), 'danger', 'heroicon-o-exclamation-triangle'],
                [R\SuratResource::class, 'Surat perlu verifikasi', 'Pengajuan surat yang baru masuk.', fn (Builder $q) => $q->whereIn('status', ['SUBMITTED', 'UNDER_VERIFICATION']), 'warning', 'heroicon-o-envelope-open'],
                [R\KrsResource::class, 'KRS siap difinalkan', 'Sudah disetujui Dosen PA.', fn (Builder $q) => $q->where('status', 'approved'), 'info', 'heroicon-o-lock-closed'],
                [R\RekomendasiPengampuResource::class, 'Rekomendasi pengampu menunggu', 'Terima/tolak kandidat dosen pengampu.', fn (Builder $q) => $q->whereIn('status', ['generated', 'reviewed']), 'info', 'heroicon-o-sparkles'],
                [R\AlertResource::class, 'Alert akademik terbuka', 'Mahasiswa yang perlu perhatian.', fn (Builder $q) => $q->whereIn('status', ['open', 'acknowledged', 'escalated']), 'danger', 'heroicon-o-bell-alert'],
            ],
            // Admin Fakultas / TU
            'admin_fakultas' => [
                [R\SuratResource::class, 'Surat menunggu approval TU', 'Langkah acknowledgement Admin Fakultas.', fn (Builder $q) => $q->where('status', 'WAITING_APPROVAL')->whereHas('approvals', fn (Builder $a) => $a->where('status', 'PENDING')->where('role_name', 'admin_fakultas')), 'warning', 'heroicon-o-inbox-arrow-down'],
                [R\SuratResource::class, 'Surat siap dinomori / dibuat', 'Sudah disetujui; beri nomor lalu Generate.', fn (Builder $q) => $q->whereIn('status', ['APPROVED', 'NUMBERED']), 'info', 'heroicon-o-hashtag'],
                [R\SuratResource::class, 'Surat siap didistribusikan', 'Dokumen sudah dibuat.', fn (Builder $q) => $q->where('status', 'GENERATED'), 'info', 'heroicon-o-paper-airplane'],
                [R\TugasAkhirResource::class, 'TA siap finalisasi', 'Semua bab wajib sudah disetujui.', fn (Builder $q) => $q->where('status', 'ready_for_finalization'), 'info', 'heroicon-o-document-check'],
                [R\RepositoryItemResource::class, 'Repositori belum dipublish', 'Periksa metadata lalu Publish.', fn (Builder $q) => $q->where('status', '!=', 'published'), 'warning', 'heroicon-o-archive-box'],
            ],
            // Kaprodi
            'kaprodi' => [
                [R\SuratResource::class, 'Surat menunggu approval Anda', 'Langkah approval Kaprodi.', fn (Builder $q) => $q->where('status', 'WAITING_APPROVAL')->whereHas('approvals', fn (Builder $a) => $a->where('status', 'PENDING')->where('role_name', 'kaprodi')), 'warning', 'heroicon-o-inbox-arrow-down'],
                [R\AlertResource::class, 'Alert dieskalasi', 'Butuh keputusan pimpinan prodi.', fn (Builder $q) => $q->where('status', 'escalated'), 'danger', 'heroicon-o-arrow-up-circle'],
                [R\RekomendasiPengampuResource::class, 'Rekomendasi pengampu menunggu', 'Validasi kandidat dosen pengampu.', fn (Builder $q) => $q->whereIn('status', ['generated', 'reviewed']), 'info', 'heroicon-o-sparkles'],
                [R\SidangResultResource::class, 'Hasil sidang belum dipublish', 'Hasil yang sudah difinalisasi.', fn (Builder $q) => $q->whereNull('published_at'), 'info', 'heroicon-o-megaphone'],
            ],
            'dekan' => $this->pimpinanTasks('dekan'),
            'wd' => $this->pimpinanTasks('wd'),
            'kbk' => [
                [R\RekomendasiPengampuResource::class, 'Rekomendasi pengampu menunggu', 'Terima/tolak kandidat sesuai keahlian.', fn (Builder $q) => $q->whereIn('status', ['generated', 'reviewed']), 'warning', 'heroicon-o-sparkles'],
                [R\DosenSertifikasiResource::class, 'Sertifikasi dosen belum divalidasi', 'Periksa bukti sertifikasi.', fn (Builder $q) => $q->where('validation_status', 'pending'), 'info', 'heroicon-o-check-badge'],
            ],
            'lpm' => [
                [R\CompetencyGapResource::class, 'Gap kompetensi tinggi', 'Mahasiswa dengan gap CPL/kompetensi tinggi.', fn (Builder $q) => $q->where('severity', 'high'), 'warning', 'heroicon-o-chart-bar'],
                [R\AlertResource::class, 'Alert akademik terbuka', 'Indikator mutu yang perlu dipantau.', fn (Builder $q) => $q->whereNotIn('status', ['resolved', 'closed']), 'info', 'heroicon-o-bell-alert'],
            ],
            'baak' => [
                [R\SidangResultResource::class, 'Hasil sidang terpublikasi', 'Bahan rekap yudisium.', fn (Builder $q) => $q->whereNotNull('published_at'), 'info', 'heroicon-o-trophy'],
                [R\TugasAkhirResource::class, 'TA belum final', 'Mahasiswa yang TA-nya belum selesai.', fn (Builder $q) => $q->whereNotIn('status', ['finalized', 'archived']), 'warning', 'heroicon-o-document-text'],
            ],
        ];

        $tasks = [];

        foreach ($this->user->getRoleNames() as $role) {
            foreach ($definitions[$role] ?? [] as [$resource, $label, $hint, $scope, $tone, $icon]) {
                if (! $this->can($resource)) {
                    continue;
                }

                if (isset($tasks[$resource . $label])) {
                    $tasks[$resource . $label]['roles'][] = $role;

                    continue;
                }

                $tasks[$resource . $label] = [
                    'roles' => [$role],
                    'label' => $label,
                    'hint' => $hint,
                    'count' => $this->count($resource, $scope),
                    'url' => $resource::getUrl(),
                    'tone' => $tone,
                    'icon' => $icon,
                ];
            }
        }

        return array_values($tasks);
    }

    /**
     * Tombol pintasan untuk pekerjaan paling sering.
     *
     * @return array<int, array{label: string, url: string, icon: string, roles: array<int, string>}>
     */
    public function quickActions(): array
    {
        $definitions = [
            'mahasiswa' => [
                [R\KrsResource::class, 'create', 'Isi KRS', 'heroicon-o-clipboard-document-list'],
                [R\SuratResource::class, 'create', 'Ajukan Surat', 'heroicon-o-envelope'],
                [R\SidangRegistrationResource::class, 'create', 'Daftar Sidang', 'heroicon-o-academic-cap'],
                [R\TaDocumentResource::class, 'index', 'Unggah Dokumen TA', 'heroicon-o-arrow-up-tray'],
                [R\StudentRecommendationResource::class, 'index', 'Lihat Rekomendasi', 'heroicon-o-sparkles'],
            ],
            'dosen' => [
                [R\JadwalKonsultasiResource::class, 'create', 'Tambah Jadwal Konsultasi', 'heroicon-o-calendar'],
                [R\DosenPublikasiResource::class, 'create', 'Tambah Publikasi', 'heroicon-o-book-open'],
            ],
            'dosen_pa' => [[R\KrsResource::class, 'index', 'Approval KRS', 'heroicon-o-clipboard-document-check']],
            'dosen_pembimbing' => [[R\TaDocumentResource::class, 'index', 'Review Dokumen TA', 'heroicon-o-document-magnifying-glass']],
            'dosen_penguji' => [[R\SidangScoreResource::class, 'create', 'Input Nilai Sidang', 'heroicon-o-pencil-square']],
            'admin_prodi' => [
                [R\SidangRegistrationResource::class, 'index', 'Verifikasi Sidang', 'heroicon-o-shield-check'],
                [R\SidangScheduleResource::class, 'create', 'Jadwalkan Sidang', 'heroicon-o-calendar-days'],
                [R\SuratResource::class, 'index', 'Verifikasi Surat', 'heroicon-o-envelope-open'],
                [R\PenawaranMataKuliahResource::class, 'index', 'Penawaran MK', 'heroicon-o-book-open'],
                [R\MahasiswaResource::class, 'index', 'Evaluasi Monitoring', 'heroicon-o-bell-alert'],
            ],
            'admin_fakultas' => [
                [R\SuratResource::class, 'index', 'Proses Surat', 'heroicon-o-envelope'],
                [R\RepositoryItemResource::class, 'index', 'Repositori TA', 'heroicon-o-archive-box'],
                [R\MahasiswaResource::class, 'create', 'Tambah Mahasiswa', 'heroicon-o-user-plus'],
                [R\DosenResource::class, 'create', 'Tambah Dosen', 'heroicon-o-user-plus'],
                [R\UserResource::class, 'index', 'Kelola Pengguna', 'heroicon-o-users'],
            ],
            'kaprodi' => [
                [R\SuratResource::class, 'index', 'Approval Surat', 'heroicon-o-inbox-arrow-down'],
                [R\AlertEscalationResource::class, 'index', 'Eskalasi Masuk', 'heroicon-o-arrow-up-circle'],
                [R\RekomendasiPengampuResource::class, 'index', 'Rekomendasi Pengampu', 'heroicon-o-sparkles'],
                [R\KrsResource::class, 'index', 'Monitoring KRS', 'heroicon-o-clipboard-document-list'],
            ],
            'dekan' => $this->pimpinanActions(),
            'wd' => $this->pimpinanActions(),
            'kbk' => [
                [R\MatriksKesesuaianResource::class, 'index', 'Matriks Kesesuaian', 'heroicon-o-table-cells'],
                [R\RekomendasiPengampuResource::class, 'index', 'Rekomendasi Pengampu', 'heroicon-o-sparkles'],
                [R\KeahlianResource::class, 'index', 'Keahlian Dosen', 'heroicon-o-light-bulb'],
                [R\MataKuliahResource::class, 'index', 'Hitung Rekomendasi MK', 'heroicon-o-calculator'],
            ],
            'lpm' => [
                [R\MahasiswaCplScoreResource::class, 'index', 'Capaian CPL', 'heroicon-o-chart-bar'],
                [R\MahasiswaGraduateProfileScoreResource::class, 'index', 'Profil Lulusan', 'heroicon-o-trophy'],
                [R\CompetencyGapResource::class, 'index', 'Gap Kompetensi', 'heroicon-o-arrow-trending-down'],
                [R\RepositoryItemResource::class, 'index', 'Repositori TA', 'heroicon-o-archive-box'],
            ],
            'baak' => [
                [R\SidangResultResource::class, 'index', 'Rekap Hasil Sidang', 'heroicon-o-trophy'],
                [R\RepositoryItemResource::class, 'index', 'Repositori Final', 'heroicon-o-archive-box'],
                [R\MahasiswaResource::class, 'index', 'Rekap Mahasiswa', 'heroicon-o-users'],
            ],
            'kepala_laboratorium' => [
                [R\DosenProfilResource::class, 'index', 'Profil Dosen', 'heroicon-o-identification'],
                [R\DosenPublikasiResource::class, 'index', 'Publikasi & Riset', 'heroicon-o-book-open'],
                [R\BebanDosenResource::class, 'index', 'Beban Dosen', 'heroicon-o-scale'],
            ],
        ];

        $actions = [];

        foreach ($this->user->getRoleNames() as $role) {
            foreach ($definitions[$role] ?? [] as [$resource, $page, $label, $icon]) {
                if (! $this->can($resource) || ($page === 'create' && ! $resource::canCreate())) {
                    continue;
                }

                if (isset($actions[$label])) {
                    $actions[$label]['roles'][] = $role;

                    continue;
                }

                $actions[$label] = ['label' => $label, 'url' => $resource::getUrl($page), 'icon' => $icon, 'roles' => [$role]];
            }
        }

        return array_values($actions);
    }

    /**
     * Ringkasan status pribadi mahasiswa.
     */
    public function studentStatus(): ?array
    {
        if (! $this->user->hasRole('mahasiswa')) {
            return null;
        }

        $mahasiswa = Mahasiswa::query()->with('programStudi')->where('user_id', $this->user->id)->first();

        if (! $mahasiswa) {
            return null;
        }

        $krs = Krs::query()->with('semester')->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $sidang = SidangRegistration::query()->with(['type', 'schedules', 'result'])->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $ta = TugasAkhir::query()->where('mahasiswa_id', $mahasiswa->id)->latest('id')->first();
        $surat = Surat::query()->with('jenisSurat')->where('requester_id', $this->user->id)->latest('id')->first();

        $progress = null;
        if ($ta) {
            try {
                $readiness = app(TaProgressService::class)->readiness($ta);
                $required = \App\Models\TaSection::query()->where('required', true)->where('status', 'active')->count();
                $progress = $required > 0 ? (int) round((($required - count($readiness['pending_sections'])) / $required) * 100) : 0;
            } catch (Throwable) {
                $progress = (int) $ta->progress_percent;
            }
        }

        $nextSchedule = $sidang?->schedules->whereIn('status', ['final', 'in_progress'])->sortBy('tanggal')->first();

        return [
            'name' => $mahasiswa->name,
            'nim' => $mahasiswa->nim,
            'prodi' => $mahasiswa->programStudi?->name,
            'semester' => $mahasiswa->semester,
            'ipk' => $mahasiswa->ipk,
            'sks' => $mahasiswa->sks_lulus,
            'krs' => $krs ? ['status' => self::statusLabel($krs->status), 'tone' => self::statusTone($krs->status), 'detail' => ($krs->semester?->code ?? '-') . ' · ' . (int) $krs->total_sks . ' SKS'] : null,
            'sidang' => $sidang ? [
                'status' => self::statusLabel($sidang->status),
                'tone' => self::statusTone($sidang->status),
                'detail' => ($sidang->type?->name ?? 'Sidang') . ($nextSchedule ? ' · ' . Carbon::parse($nextSchedule->tanggal)->translatedFormat('d M Y') . ' ' . substr((string) $nextSchedule->jam_mulai, 0, 5) : ($sidang->result ? ' · Nilai ' . $sidang->result->final_grade : '')),
            ] : null,
            'ta' => $ta ? ['judul' => $ta->judul, 'progress' => $progress, 'status' => self::statusLabel($ta->status)] : null,
            'surat' => $surat ? ['status' => self::statusLabel($surat->status), 'tone' => self::statusTone($surat->status), 'detail' => $surat->jenisSurat?->name ?? $surat->subject] : null,
        ];
    }

    /**
     * Agenda sidang mendatang yang relevan (mahasiswa: miliknya, dosen: yang diuji/dibimbing).
     */
    public function agenda(): array
    {
        if (! $this->can(R\SidangScheduleResource::class)) {
            return [];
        }

        return R\SidangScheduleResource::getEloquentQuery()
            ->with(['registration.mahasiswa', 'registration.type', 'ruangan'])
            ->whereIn('status', ['final', 'validated', 'rescheduled', 'in_progress'])
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(5)
            ->get()
            ->map(fn ($schedule) => [
                'date' => Carbon::parse($schedule->tanggal)->translatedFormat('D, d M'),
                'time' => substr((string) $schedule->jam_mulai, 0, 5) . '–' . substr((string) $schedule->jam_selesai, 0, 5),
                'title' => ($schedule->registration?->type?->name ?? 'Sidang') . ' · ' . ($schedule->registration?->mahasiswa?->name ?? '-'),
                'place' => $schedule->ruangan?->name ?? ucfirst((string) $schedule->mode),
            ])
            ->all();
    }

    public function showsRiskOverview(): bool
    {
        return $this->user->hasAnyRole(['admin_fakultas', 'admin_prodi', 'kaprodi', 'dekan', 'wd', 'lpm', 'baak']);
    }

    private function pimpinanTasks(string $role): array
    {
        return [
            [R\SuratResource::class, 'Surat menunggu approval Anda', 'Surat strategis yang perlu persetujuan.', fn (Builder $q) => $q->where('status', 'WAITING_APPROVAL')->whereHas('approvals', fn (Builder $a) => $a->where('status', 'PENDING')->where('role_name', $role)), 'warning', 'heroicon-o-inbox-arrow-down'],
            [R\AlertResource::class, 'Alert risiko tinggi', 'Mahasiswa berisiko tinggi di fakultas.', fn (Builder $q) => $q->where('severity', 'high')->whereNotIn('status', ['resolved', 'closed']), 'danger', 'heroicon-o-bell-alert'],
        ];
    }

    private function pimpinanActions(): array
    {
        return [
            [R\SuratResource::class, 'index', 'Approval Surat', 'heroicon-o-inbox-arrow-down'],
            [R\AlertResource::class, 'index', 'Monitoring Risiko', 'heroicon-o-bell-alert'],
            [R\BebanDosenResource::class, 'index', 'Peta Beban Dosen', 'heroicon-o-scale'],
            [R\MahasiswaGraduateProfileScoreResource::class, 'index', 'Sebaran Profil Lulusan', 'heroicon-o-trophy'],
        ];
    }

    public function hasFeature(string $resource): bool
    {
        return $this->can($resource);
    }

    private function can(string $resource): bool
    {
        return in_array($resource, $this->allowed, true)
            && RoleFeatureRegistry::registeredInPanel($resource)
            && $resource::canViewAny();
    }

    private function count(string $resource, callable $scope): int
    {
        try {
            return (int) $scope($resource::getEloquentQuery())->count();
        } catch (Throwable $e) {
            report($e);

            return 0;
        }
    }

    public static function statusLabel(?string $status): string
    {
        return [
            'draft' => 'Draft', 'DRAFT' => 'Draft',
            'submitted' => 'Diajukan', 'SUBMITTED' => 'Diajukan',
            'waiting_pa' => 'Menunggu Dosen PA',
            'revision_required' => 'Perlu Revisi', 'REVISION_REQUIRED' => 'Perlu Revisi',
            'approved' => 'Disetujui', 'APPROVED' => 'Disetujui',
            'final' => 'Final', 'finalized' => 'Final',
            'under_verification' => 'Diverifikasi', 'UNDER_VERIFICATION' => 'Diverifikasi',
            'verified' => 'Terverifikasi', 'VERIFIED' => 'Terverifikasi',
            'ready_for_plotting' => 'Menunggu Plotting',
            'scheduled' => 'Terjadwal',
            'revision' => 'Revisi',
            'completed' => 'Selesai',
            'WAITING_APPROVAL' => 'Menunggu Approval',
            'NUMBERED' => 'Bernomor', 'GENERATED' => 'Dokumen Siap',
            'DISTRIBUTED' => 'Terdistribusi', 'ARCHIVED' => 'Selesai / Arsip',
            'REJECTED' => 'Ditolak', 'rejected' => 'Ditolak',
            'active' => 'Berjalan', 'in_review' => 'Direview',
            'ready_for_finalization' => 'Siap Finalisasi',
        ][$status] ?? ucfirst(str_replace('_', ' ', strtolower((string) $status)));
    }

    public static function statusTone(?string $status): string
    {
        $status = strtolower((string) $status);

        return match (true) {
            in_array($status, ['final', 'finalized', 'completed', 'approved', 'archived', 'distributed', 'generated', 'numbered', 'verified', 'scheduled'], true) => 'success',
            in_array($status, ['revision_required', 'rejected', 'revision'], true) => 'danger',
            in_array($status, ['draft'], true) => 'gray',
            default => 'warning',
        };
    }
}
