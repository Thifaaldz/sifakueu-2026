<?php

namespace App\Support\Access;

use App\Filament\Admin\Resources as R;
use Filament\Resources\Resource;

/**
 * Daftar fitur (menu) per role sesuai dokumen "Aktor, Menu, dan Hak Akses SIFAK".
 *
 * Menu yang tampil = fitur role ini ∩ permission user (canViewAny). Akun multi-role
 * (mis. dosen PA + pembimbing + penguji) mendapat gabungan fitur. Resource di luar
 * daftar ini ditolak oleh middleware EnforceRoleFeatures.
 */
class RoleFeatureRegistry
{
    /** Urutan grup di sidebar & dashboard. */
    public const GROUP_ORDER = [
        'Akademik',
        'Perwalian (PA)',
        'Sidang',
        'Sidang & Pengujian',
        'Bimbingan TA',
        'Dokumen TA',
        'Surat',
        'Monitoring',
        'Profil & Rekomendasi',
        'Profil Saya',
        'KRS & Penjadwalan',
        'Profiling Dosen',
        'Profiling Mahasiswa',
        'CPL & Profil Lulusan',
        'Data Master',
        'Pengguna & Akses',
        'Layanan Sistem',
    ];

    public const GROUP_ICONS = [
        'Akademik' => 'heroicon-o-book-open',
        'Perwalian (PA)' => 'heroicon-o-user-group',
        'Sidang' => 'heroicon-o-academic-cap',
        'Sidang & Pengujian' => 'heroicon-o-academic-cap',
        'Bimbingan TA' => 'heroicon-o-document-text',
        'Dokumen TA' => 'heroicon-o-document-text',
        'Surat' => 'heroicon-o-envelope',
        'Monitoring' => 'heroicon-o-bell-alert',
        'Profil & Rekomendasi' => 'heroicon-o-sparkles',
        'Profil Saya' => 'heroicon-o-identification',
        'KRS & Penjadwalan' => 'heroicon-o-calendar-days',
        'Profiling Dosen' => 'heroicon-o-identification',
        'Profiling Mahasiswa' => 'heroicon-o-chart-bar',
        'CPL & Profil Lulusan' => 'heroicon-o-trophy',
        'Data Master' => 'heroicon-o-circle-stack',
        'Pengguna & Akses' => 'heroicon-o-shield-check',
        'Layanan Sistem' => 'heroicon-o-cog-6-tooth',
    ];

    public const ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'admin_fakultas' => 'Admin Fakultas / TU',
        'admin_prodi' => 'Admin Prodi',
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'dosen_pa' => 'Dosen PA',
        'dosen_pembimbing' => 'Dosen Pembimbing',
        'dosen_penguji' => 'Dosen Penguji',
        'kaprodi' => 'Kaprodi',
        'dekan' => 'Dekan',
        'wd' => 'Wakil Dekan',
        'kbk' => 'Koordinator KBK',
        'lpm' => 'LPM / Gugus Mutu',
        'baak' => 'BAAK',
        'kepala_laboratorium' => 'Kepala Laboratorium',
        'alumni' => 'Alumni',
    ];

    /**
     * @return array<string, array<string, array<class-string<resource>, string>>> role => group => [resource => label]
     */
    public static function features(): array
    {
        $profilDosenLengkap = [
            R\DosenProfilResource::class => 'Profil Dosen',
            R\KeahlianResource::class => 'Keahlian',
            R\DosenPendidikanResource::class => 'Pendidikan',
            R\DosenSertifikasiResource::class => 'Sertifikasi',
            R\DosenPublikasiResource::class => 'Publikasi',
            R\DosenPengalamanIndustriResource::class => 'Pengalaman Industri',
            R\RiwayatMengajarResource::class => 'Riwayat Mengajar',
        ];

        $cplProfilLulusan = [
            R\CplResource::class => 'CPL',
            R\PloResource::class => 'PLO',
            R\PemetaanMkCplResource::class => 'Pemetaan MK–CPL',
            R\PemetaanCplPloResource::class => 'Pemetaan CPL–PLO',
            R\GraduateProfileResource::class => 'Profil Lulusan',
        ];

        $capaianMahasiswa = [
            R\MahasiswaProfileResource::class => 'Profil Mahasiswa',
            R\MahasiswaCplScoreResource::class => 'Skor CPL',
            R\MahasiswaPloScoreResource::class => 'Skor PLO',
            R\MahasiswaGraduateProfileScoreResource::class => 'Skor Profil Lulusan',
            R\CompetencyGapResource::class => 'Gap Kompetensi',
            R\StudentRecommendationResource::class => 'Rekomendasi Mahasiswa',
        ];

        return [
            'mahasiswa' => [
                'Akademik' => [
                    R\KrsResource::class => 'KRS Saya',
                    R\KrsDetailResource::class => 'Mata Kuliah Diambil',
                    R\KrsValidationResultResource::class => 'Hasil Validasi KRS',
                    R\JadwalKuliahResource::class => 'Jadwal Kuliah',
                ],
                'Sidang' => [
                    R\SidangRequirementResource::class => 'Persyaratan Sidang',
                    R\SidangRegistrationResource::class => 'Pendaftaran Sidang',
                    R\SidangScheduleResource::class => 'Jadwal Sidang',
                    R\SidangResultResource::class => 'Hasil Sidang',
                    R\SidangRevisionResource::class => 'Revisi Sidang',
                    R\SidangMinuteResource::class => 'Berita Acara',
                ],
                'Dokumen TA' => [
                    R\TugasAkhirResource::class => 'Tugas Akhir Saya',
                    R\TaDocumentResource::class => 'Dokumen per Bab',
                    R\TaDocumentVersionResource::class => 'Riwayat Versi',
                    R\TaCommentResource::class => 'Komentar Pembimbing',
                    R\RepositoryItemResource::class => 'Repositori TA',
                    R\JadwalKonsultasiResource::class => 'Jadwal Konsultasi Dosen',
                ],
                'Surat' => [
                    R\SuratResource::class => 'Pengajuan Surat',
                    R\GeneratedLetterResource::class => 'Dokumen Surat',
                ],
                'Monitoring' => [
                    R\AlertResource::class => 'Peringatan Akademik',
                    R\MonitoringSnapshotResource::class => 'Riwayat Evaluasi',
                ],
                'Profil & Rekomendasi' => [
                    R\MahasiswaProfileResource::class => 'Profil Saya',
                    R\MahasiswaInterestResource::class => 'Minat',
                    R\MahasiswaCertificationResource::class => 'Sertifikasi',
                    R\MahasiswaPortfolioResource::class => 'Portofolio',
                    R\MahasiswaOrganizationResource::class => 'Organisasi',
                    R\MahasiswaMbkmResource::class => 'MBKM / Magang',
                    R\MahasiswaCplScoreResource::class => 'Skor CPL',
                    R\MahasiswaGraduateProfileScoreResource::class => 'Skor Profil Lulusan',
                    R\CompetencyGapResource::class => 'Gap Kompetensi',
                    R\StudentRecommendationResource::class => 'Rekomendasi Untuk Saya',
                ],
            ],

            'dosen' => [
                'Akademik' => [
                    R\JadwalKuliahResource::class => 'Jadwal Mengajar',
                    R\BebanDosenResource::class => 'Beban Dosen',
                    R\JadwalKonsultasiResource::class => 'Jadwal Konsultasi',
                    R\DosenLokasiResource::class => 'Lokasi & Ketersediaan',
                ],
                'Profil Saya' => $profilDosenLengkap + [
                    R\DosenPreferensiMkResource::class => 'Preferensi Mata Kuliah',
                ],
                'Surat' => [
                    R\SuratResource::class => 'Pengajuan Surat',
                    R\GeneratedLetterResource::class => 'Dokumen Surat',
                ],
            ],
            'dosen_pa' => [
                'Perwalian (PA)' => [
                    R\KrsResource::class => 'Approval KRS',
                    R\KrsDetailResource::class => 'Rincian KRS',
                    R\AlertResource::class => 'Alert Mahasiswa PA',
                    R\AlertFollowupResource::class => 'Riwayat Tindak Lanjut',
                    R\MahasiswaProfileResource::class => 'Profil Mahasiswa PA',
                    R\StudentRecommendationResource::class => 'Rekomendasi Mahasiswa',
                ],
            ],
            'dosen_pembimbing' => [
                'Bimbingan TA' => [
                    R\TugasAkhirResource::class => 'Mahasiswa Bimbingan',
                    R\TaDocumentResource::class => 'Review Dokumen TA',
                    R\TaDocumentVersionResource::class => 'Riwayat Versi',
                    R\TaReviewResource::class => 'Riwayat Review',
                    R\TaCommentResource::class => 'Komentar',
                    R\TaApprovalResource::class => 'Approval Bab',
                    R\TaProgressLogResource::class => 'Progres TA',
                ],
            ],
            'dosen_penguji' => [
                'Sidang & Pengujian' => [
                    R\SidangScheduleResource::class => 'Jadwal Menguji',
                    R\SidangRegistrationResource::class => 'Detail Sidang',
                    R\SidangScoreResource::class => 'Penilaian Sidang',
                    R\SidangRevisionResource::class => 'Catatan / Revisi',
                    R\SidangResultResource::class => 'Hasil Sidang',
                ],
            ],

            'admin_prodi' => [
                'KRS & Penjadwalan' => [
                    R\PeriodeKrsResource::class => 'Periode KRS',
                    R\PenawaranMataKuliahResource::class => 'Penawaran Mata Kuliah',
                    R\KrsResource::class => 'KRS Mahasiswa',
                    R\KrsDetailResource::class => 'Rincian KRS',
                    R\KrsValidationResultResource::class => 'Hasil Validasi KRS',
                    R\KelasKuliahResource::class => 'Kelas Kuliah',
                    R\PlottingDosenResource::class => 'Plotting Dosen',
                    R\JadwalKuliahResource::class => 'Jadwal Kuliah',
                    R\JadwalConflictResource::class => 'Konflik Jadwal',
                    R\JadwalHistoryResource::class => 'Riwayat Jadwal',
                ],
                'Sidang' => [
                    R\SidangTypeResource::class => 'Jenis Sidang',
                    R\SidangRequirementResource::class => 'Persyaratan',
                    R\SidangRegistrationResource::class => 'Pendaftaran & Verifikasi',
                    R\SidangAssignmentResource::class => 'Plotting Penguji',
                    R\SidangScheduleResource::class => 'Jadwal Sidang',
                    R\SidangRubricResource::class => 'Rubrik Penilaian',
                    R\SidangScoreResource::class => 'Nilai Sidang',
                    R\SidangResultResource::class => 'Hasil Sidang',
                    R\SidangRevisionResource::class => 'Revisi Sidang',
                    R\SidangMinuteResource::class => 'Berita Acara',
                ],
                'Surat' => [
                    R\SuratResource::class => 'Verifikasi Surat',
                    R\SuratApprovalResource::class => 'Riwayat Approval',
                ],
                'Monitoring' => [
                    R\AlertResource::class => 'Alert Akademik',
                    R\AlertFollowupResource::class => 'Tindak Lanjut',
                    R\AlertEscalationResource::class => 'Eskalasi',
                    R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
                    R\MonitoringIndicatorResultResource::class => 'Hasil Indikator',
                    R\MonitoringRuleResource::class => 'Aturan Monitoring',
                    R\MonitoringOverrideResource::class => 'Override Status',
                ],
                'Profiling Dosen' => $profilDosenLengkap + [
                    R\DosenPreferensiMkResource::class => 'Preferensi Mata Kuliah',
                    R\BebanDosenResource::class => 'Beban Dosen',
                    R\MatriksKesesuaianResource::class => 'Matriks Kesesuaian',
                    R\RekomendasiPengampuResource::class => 'Rekomendasi Pengampu',
                    R\JadwalKonsultasiResource::class => 'Jadwal Konsultasi',
                ],
                'Profiling Mahasiswa' => $capaianMahasiswa + [
                    R\MahasiswaInterestResource::class => 'Minat Mahasiswa',
                    R\MahasiswaCertificationResource::class => 'Sertifikasi Mahasiswa',
                    R\MahasiswaPortfolioResource::class => 'Portofolio Mahasiswa',
                    R\MahasiswaOrganizationResource::class => 'Organisasi Mahasiswa',
                    R\MahasiswaMbkmResource::class => 'MBKM / Magang',
                    R\RecommendationHistoryResource::class => 'Riwayat Rekomendasi',
                ],
                'CPL & Profil Lulusan' => $cplProfilLulusan,
                'Dokumen TA' => [
                    R\TugasAkhirResource::class => 'Tugas Akhir',
                    R\TaDocumentResource::class => 'Dokumen TA',
                    R\TaProgressLogResource::class => 'Progres TA',
                    R\RepositoryItemResource::class => 'Repositori TA',
                ],
                'Data Master' => [
                    R\MahasiswaResource::class => 'Mahasiswa',
                    R\DosenResource::class => 'Dosen',
                    R\MataKuliahResource::class => 'Mata Kuliah',
                    R\KurikulumResource::class => 'Kurikulum',
                    R\SemesterResource::class => 'Semester',
                    R\RuanganResource::class => 'Ruangan',
                ],
            ],

            'admin_fakultas' => [
                'Surat' => [
                    R\SuratResource::class => 'Pengajuan Surat',
                    R\SuratApprovalResource::class => 'Riwayat Approval',
                    R\GeneratedLetterResource::class => 'Dokumen Surat',
                    R\LetterDistributionResource::class => 'Distribusi',
                    R\LetterArchiveResource::class => 'Arsip Surat',
                    R\LetterNumberResource::class => 'Nomor Surat',
                    R\LetterVerificationResource::class => 'Log Verifikasi',
                    R\LetterVerificationTokenResource::class => 'Token Verifikasi',
                    R\JenisSuratResource::class => 'Jenis Surat',
                    R\LetterFormFieldResource::class => 'Form Field Surat',
                    R\LetterTemplateResource::class => 'Template Surat',
                    R\LetterNumberSequenceResource::class => 'Sequence Nomor',
                    R\ApprovalFlowResource::class => 'Alur Approval',
                    R\ApprovalFlowStepResource::class => 'Langkah Approval',
                ],
                'Sidang' => [
                    R\SidangRegistrationResource::class => 'Pendaftaran Sidang',
                    R\SidangScheduleResource::class => 'Jadwal Sidang',
                    R\SidangResultResource::class => 'Hasil Sidang',
                    R\SidangMinuteResource::class => 'Berita Acara',
                ],
                'Monitoring' => [
                    R\AlertResource::class => 'Alert Akademik',
                    R\AlertEscalationResource::class => 'Eskalasi',
                    R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
                ],
                'Dokumen TA' => [
                    R\TugasAkhirResource::class => 'Tugas Akhir',
                    R\TaDocumentResource::class => 'Dokumen TA',
                    R\TaSectionResource::class => 'Struktur Bab',
                    R\TaProgressLogResource::class => 'Progres TA',
                    R\RepositoryItemResource::class => 'Repositori TA',
                    R\RevisionCycleResource::class => 'Siklus Revisi',
                ],
                'Data Master' => [
                    R\FakultasResource::class => 'Profil Fakultas',
                    R\ProgramStudiResource::class => 'Program Studi',
                    R\MahasiswaResource::class => 'Mahasiswa',
                    R\DosenResource::class => 'Dosen',
                    R\MataKuliahResource::class => 'Mata Kuliah',
                    R\KurikulumResource::class => 'Kurikulum',
                    R\RuanganResource::class => 'Ruangan',
                    R\TahunAkademikResource::class => 'Tahun Akademik',
                    R\SemesterResource::class => 'Semester',
                    R\RumpunIlmuResource::class => 'Rumpun Ilmu',
                    R\KbkResource::class => 'KBK',
                ],
                'Pengguna & Akses' => [
                    R\UserResource::class => 'Pengguna',
                    \BezhanSalleh\FilamentShield\Resources\RoleResource::class => 'Role & Permission',
                ],
                'Layanan Sistem' => [
                    R\AuditLogResource::class => 'Audit Log',
                    R\SifakNotificationResource::class => 'Notifikasi',
                    R\NotificationTemplateResource::class => 'Template Notifikasi',
                    R\WorkflowHistoryResource::class => 'Riwayat Workflow',
                    R\StoredFileResource::class => 'File Tersimpan',
                    R\ScheduledTaskLogResource::class => 'Log Tugas Terjadwal',
                ],
            ],

            'kaprodi' => [
                'Monitoring' => [
                    R\AlertResource::class => 'Alert Mahasiswa',
                    R\AlertEscalationResource::class => 'Eskalasi Masuk',
                    R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
                    R\MonitoringOverrideResource::class => 'Override Status',
                ],
                'Surat' => [
                    R\SuratResource::class => 'Approval Surat',
                    R\SuratApprovalResource::class => 'Riwayat Approval',
                ],
                'Sidang' => [
                    R\SidangRegistrationResource::class => 'Pendaftaran Sidang',
                    R\SidangAssignmentResource::class => 'Plotting Penguji',
                    R\SidangScheduleResource::class => 'Jadwal Sidang',
                    R\SidangResultResource::class => 'Hasil Sidang',
                    R\SidangRevisionResource::class => 'Revisi Sidang',
                ],
                'KRS & Penjadwalan' => [
                    R\KrsResource::class => 'Monitoring KRS',
                    R\JadwalKuliahResource::class => 'Jadwal Kuliah',
                    R\PlottingDosenResource::class => 'Plotting Dosen',
                    R\JadwalConflictResource::class => 'Konflik Jadwal',
                ],
                'Profiling Dosen' => [
                    R\DosenProfilResource::class => 'Profil Dosen',
                    R\BebanDosenResource::class => 'Beban Dosen',
                    R\MatriksKesesuaianResource::class => 'Matriks Kesesuaian',
                    R\RekomendasiPengampuResource::class => 'Rekomendasi Pengampu',
                ],
                'Profiling Mahasiswa' => $capaianMahasiswa,
                'CPL & Profil Lulusan' => $cplProfilLulusan,
                'Dokumen TA' => [
                    R\TugasAkhirResource::class => 'Tugas Akhir',
                    R\TaDocumentResource::class => 'Dokumen TA',
                    R\TaProgressLogResource::class => 'Progres TA',
                ],
            ],
            'dekan' => self::pimpinanFakultas(),
            'wd' => self::pimpinanFakultas(),
            'kbk' => [
                'Profiling Dosen' => $profilDosenLengkap + [
                    R\BebanDosenResource::class => 'Beban Dosen',
                    R\MatriksKesesuaianResource::class => 'Matriks Kesesuaian',
                    R\RekomendasiPengampuResource::class => 'Rekomendasi Pengampu',
                ],
                'Data Master' => [
                    R\RumpunIlmuResource::class => 'Rumpun Ilmu',
                    R\KbkResource::class => 'KBK',
                    R\MataKuliahResource::class => 'Mata Kuliah',
                    R\DosenResource::class => 'Dosen',
                ],
            ],
            'lpm' => [
                'CPL & Profil Lulusan' => [
                    R\CplResource::class => 'CPL',
                    R\PloResource::class => 'PLO',
                    R\GraduateProfileResource::class => 'Profil Lulusan',
                ],
                'Profiling Mahasiswa' => [
                    R\MahasiswaCplScoreResource::class => 'Capaian CPL',
                    R\MahasiswaPloScoreResource::class => 'Capaian PLO',
                    R\MahasiswaGraduateProfileScoreResource::class => 'Skor Profil Lulusan',
                    R\CompetencyGapResource::class => 'Gap CPL / Kompetensi',
                    R\MahasiswaProfileResource::class => 'Profil Mahasiswa',
                ],
                'Monitoring' => [
                    R\AlertResource::class => 'Alert Akademik',
                    R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
                ],
                'Dokumen TA' => [
                    R\RepositoryItemResource::class => 'Repositori TA',
                    R\TugasAkhirResource::class => 'Tugas Akhir',
                ],
            ],
            'baak' => [
                'Sidang' => [
                    R\SidangRegistrationResource::class => 'Rekap Sidang',
                    R\SidangResultResource::class => 'Hasil Sidang',
                    R\SidangMinuteResource::class => 'Berita Acara',
                ],
                'Monitoring' => [
                    R\AlertResource::class => 'Alert Akademik',
                    R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
                ],
                'Dokumen TA' => [
                    R\TugasAkhirResource::class => 'Status Tugas Akhir',
                    R\RepositoryItemResource::class => 'Repositori Final',
                ],
                'Profiling Mahasiswa' => [
                    R\MahasiswaProfileResource::class => 'Rekap Profil Mahasiswa',
                ],
                'Data Master' => [
                    R\MahasiswaResource::class => 'Rekap Mahasiswa',
                ],
            ],
            'kepala_laboratorium' => [
                'Profiling Dosen' => [
                    R\DosenProfilResource::class => 'Profil Dosen',
                    R\KeahlianResource::class => 'Keahlian',
                    R\DosenPublikasiResource::class => 'Publikasi & Riset',
                    R\BebanDosenResource::class => 'Beban Dosen',
                ],
                'Data Master' => [
                    R\DosenResource::class => 'Dosen',
                    R\MahasiswaResource::class => 'Mahasiswa',
                    R\MataKuliahResource::class => 'Mata Kuliah',
                ],
            ],
        ];
    }

    private static function pimpinanFakultas(): array
    {
        return [
            'Monitoring' => [
                R\AlertResource::class => 'Monitoring Risiko',
                R\MonitoringSnapshotResource::class => 'Snapshot Evaluasi',
            ],
            'Surat' => [
                R\SuratResource::class => 'Approval Surat',
                R\SuratApprovalResource::class => 'Riwayat Approval',
            ],
            'Profiling Dosen' => [
                R\DosenProfilResource::class => 'Profil Dosen',
                R\KeahlianResource::class => 'Peta Keahlian',
                R\BebanDosenResource::class => 'Peta Beban Dosen',
                R\MatriksKesesuaianResource::class => 'Matriks Kesesuaian',
            ],
            'Profiling Mahasiswa' => [
                R\MahasiswaProfileResource::class => 'Profil Mahasiswa',
                R\MahasiswaGraduateProfileScoreResource::class => 'Sebaran Profil Lulusan',
                R\MahasiswaCplScoreResource::class => 'Capaian CPL',
            ],
            'CPL & Profil Lulusan' => [
                R\CplResource::class => 'CPL',
                R\PloResource::class => 'PLO',
                R\GraduateProfileResource::class => 'Profil Lulusan',
            ],
        ];
    }

    /**
     * Gabungan fitur untuk sekumpulan role, terurut sesuai GROUP_ORDER.
     *
     * @param  array<int, string>  $roles
     * @return array<string, array<class-string<resource>, string>>
     */
    public static function forRoles(array $roles): array
    {
        $features = self::features();
        $merged = [];

        foreach ($roles as $role) {
            foreach ($features[$role] ?? [] as $group => $items) {
                foreach ($items as $resource => $label) {
                    if (! self::alreadyListed($merged, $resource)) {
                        $merged[$group][$resource] = $label;
                    }
                }
            }
        }

        uksort($merged, fn (string $a, string $b) => self::groupIndex($a) <=> self::groupIndex($b));

        return $merged;
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, class-string<resource>>
     */
    public static function allowedResources(array $roles): array
    {
        return collect(self::forRoles($roles))->flatMap(fn (array $items) => array_keys($items))->unique()->values()->all();
    }

    public static function hasFeatures(array $roles): bool
    {
        return collect($roles)->contains(fn (string $role) => array_key_exists($role, self::features()));
    }

    public static function roleLabels(array $roles): array
    {
        return collect($roles)->map(fn (string $role) => self::ROLE_LABELS[$role] ?? $role)->values()->all();
    }

    /**
     * Fitur yang benar-benar dapat dibuka user (registry ∩ permission).
     *
     * @return array<string, array<int, array{resource: class-string<resource>, label: string, url: string, icon: ?string}>>
     */
    public static function visibleFor($user): array
    {
        if (! $user) {
            return [];
        }

        $result = [];

        foreach (self::forRoles($user->getRoleNames()->all()) as $group => $items) {
            foreach ($items as $resource => $label) {
                /** @var class-string<resource> $resource */
                if (! class_exists($resource) || ! self::registeredInPanel($resource) || ! $resource::canViewAny()) {
                    continue;
                }

                $result[$group][] = [
                    'resource' => $resource,
                    'label' => $label,
                    'url' => $resource::getUrl(),
                    'icon' => $resource::getNavigationIcon(),
                ];
            }
        }

        return $result;
    }

    public static function registeredInPanel(string $resource): bool
    {
        return in_array($resource, \Filament\Facades\Filament::getCurrentPanel()?->getResources() ?? [], true);
    }

    private static function alreadyListed(array $merged, string $resource): bool
    {
        foreach ($merged as $items) {
            if (array_key_exists($resource, $items)) {
                return true;
            }
        }

        return false;
    }

    private static function groupIndex(string $group): int
    {
        $index = array_search($group, self::GROUP_ORDER, true);

        return $index === false ? 999 : $index;
    }
}
