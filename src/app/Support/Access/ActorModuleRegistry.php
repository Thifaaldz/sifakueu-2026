<?php

namespace App\Support\Access;

class ActorModuleRegistry
{
    public static function forRoles(array $roles): array
    {
        $modules = [];

        foreach ($roles as $role) {
            if (isset(self::modules()[$role])) {
                $modules[$role] = self::modules()[$role];
            }
        }

        return $modules;
    }

    public static function modules(): array
    {
        return [
            'super_admin' => [
                'label' => 'Super Admin Platform',
                'panel' => 'https://sifakueu.test/admin',
                'scope' => 'Platform',
                'menus' => [
                    'Dashboard',
                    'Tenant Management: Semua Tenant, Tambah Fakultas, Tenant Pending, Tenant Suspended, Provisioning Failed',
                    'Domain & Subdomain: Domain Tenant, Reserved Subdomain, Domain Validation',
                    'Provisioning: Provisioning Queue, Provisioning History, Migration Status, Retry Provisioning',
                    'Tenant Health: Database Health, Storage Health, Queue Status, Scheduler Status, Last Error',
                    'Access Management: Super Admin, Platform Operator, Tenant Admin',
                    'Backup & Restore: Backup Tenant, Backup History, Restore Tenant',
                    'Logs & Audit: Platform Audit, Provisioning Log, Tenant Audit Summary, Error Log',
                    'System: Application Version, Schema Version, Queue Worker, Scheduler, Maintenance',
                    'Settings: Base Domain, Notification, Storage, Security',
                ],
                'allowed' => [
                    'Melihat seluruh tenant',
                    'Membuat tenant baru dan subdomain tenant',
                    'Menjalankan provisioning, retry provisioning, backup, dan restore',
                    'Melihat health tenant, status migration, audit platform, dan konfigurasi global',
                    'Suspend dan reactivate tenant',
                ],
                'denied' => [
                    'Tidak melakukan approval akademik tenant',
                    'Tidak menginput nilai sidang',
                    'Tidak mengubah KRS atau dokumen TA mahasiswa secara default',
                ],
            ],
            'admin_fakultas' => [
                'label' => 'Admin Tenant / Admin Fakultas Utama',
                'panel' => '{tenant}.sifakueu.test/admin',
                'scope' => 'Tenant',
                'menus' => [
                    'Dashboard',
                    'Profil Fakultas',
                    'Program Studi',
                    'Master Data: Mahasiswa, Dosen, Mata Kuliah, Ruangan, Kurikulum, Semester, Tahun Akademik',
                    'User & Role Tenant: User, Role, Permission Tenant',
                    'Surat Menyurat: Pengajuan, Verifikasi, Nomor Surat, Template, Arsip',
                    'Sidang',
                    'Monitoring',
                    'Repositori TA',
                    'Laporan',
                    'Konfigurasi Tenant',
                ],
                'allowed' => [
                    'Mengelola data fakultas tenant sendiri',
                    'Mengelola user, role, master data, dan konfigurasi tenant',
                    'Mengelola administrasi surat, arsip, repositori TA, dan laporan fakultas',
                ],
                'denied' => [
                    'Tidak membuat tenant baru',
                    'Tidak mengakses tenant lain',
                    'Tidak mengubah base domain atau Super Admin platform',
                    'Tidak approve KRS atau mengubah nilai akademik',
                ],
            ],
            'admin_prodi' => [
                'label' => 'Admin Prodi',
                'panel' => '{tenant}.sifakueu.test/admin',
                'scope' => 'Prodi',
                'menus' => [
                    'Dashboard Prodi',
                    'Mahasiswa',
                    'Dosen',
                    'Mata Kuliah',
                    'Kurikulum',
                    'KRS',
                    'Penjadwalan',
                    'Sidang: Pendaftaran, Verifikasi, Plotting Penguji, Jadwal',
                    'Profiling Dosen',
                    'Profiling Mahasiswa',
                    'Monitoring',
                    'Dokumen TA',
                    'Laporan Prodi',
                ],
                'allowed' => [
                    'Mengelola mahasiswa, dosen, mata kuliah, dan kurikulum prodi',
                    'Verifikasi sidang, plotting penguji, dan kelola jadwal',
                    'Melihat monitoring, profiling, dokumen TA, dan laporan prodi',
                ],
                'denied' => [
                    'Tidak mengelola tenant/platform',
                    'Tidak approval tingkat dekan',
                    'Tidak mengakses data prodi lain tanpa izin',
                ],
            ],
            'mahasiswa' => [
                'label' => 'Mahasiswa',
                'panel' => '{tenant}.sifakueu.test/mahasiswa',
                'scope' => 'Diri sendiri',
                'menus' => [
                    'Dashboard',
                    'Profil Saya',
                    'KRS',
                    'Jadwal Kuliah',
                    'Sidang: Daftar Sempro, Daftar Sidang TA, Jadwal Sidang, Hasil, Revisi',
                    'Surat: Ajukan Surat, Status Surat, Riwayat Surat',
                    'Monitoring: Progress Akademik, Alert, Progress TA',
                    'Profil Lulusan: Skor CPL, Rekomendasi PLO, Rekomendasi MK, Rekomendasi Karier, Rekomendasi Topik TA',
                    'Bimbingan: Dosen Pembimbing, Jadwal Konsultasi, Ketersediaan Dosen',
                    'Dokumen TA: Judul, Daftar Isi, Daftar Pustaka, Bab 1-5, Log Revisi, File Final',
                    'Notifikasi',
                ],
                'allowed' => [
                    'Melihat dan mengubah profil sendiri',
                    'Mengisi KRS dan melihat status approval',
                    'Mendaftar sidang, mengajukan surat, dan mengelola dokumen TA sendiri',
                    'Melihat alert, skor CPL/PLO, rekomendasi, komentar pembimbing, dan jadwal konsultasi terkait',
                ],
                'denied' => [
                    'Tidak melihat data mahasiswa lain',
                    'Tidak approve KRS sendiri',
                    'Tidak menetapkan penguji atau mengubah nilai',
                    'Tidak mengakses dokumen TA mahasiswa lain',
                ],
            ],
            'dosen' => [
                'label' => 'Dosen',
                'panel' => '{tenant}.sifakueu.test/dosen',
                'scope' => 'Diri sendiri / assignment',
                'menus' => [
                    'Dashboard',
                    'Profil Saya',
                    'Jadwal Mengajar',
                    'Beban Dosen',
                    'Mahasiswa Bimbingan',
                    'Jadwal Konsultasi',
                    'Profil Keahlian',
                    'Riwayat Mengajar',
                    'Notifikasi',
                ],
                'allowed' => [
                    'Melihat profil sendiri',
                    'Memperbarui data profil tertentu',
                    'Melihat jadwal mengajar dan beban sendiri',
                    'Mengelola jadwal konsultasi',
                    'Melihat mahasiswa yang menjadi tanggung jawabnya',
                    'Melihat rekomendasi mata kuliah yang relevan untuk dirinya',
                ],
                'denied' => [
                    'Tidak mengubah profil dosen lain',
                    'Tidak melihat data akademik seluruh mahasiswa',
                    'Akses approval KRS, review TA, dan penilaian sidang tetap mengikuti assignment mahasiswa/sidang terkait',
                ],
            ],
            'dosen_pa' => [
                'label' => 'Dosen PA',
                'panel' => '{tenant}.sifakueu.test/dosen',
                'scope' => 'Mahasiswa PA',
                'menus' => [
                    'Dashboard',
                    'Profil Saya',
                    'Jadwal Mengajar',
                    'Beban Dosen',
                    'Mahasiswa PA',
                    'KRS Approval',
                    'Monitoring Akademik',
                    'Alert Mahasiswa PA',
                    'Jadwal Konsultasi',
                    'Notifikasi',
                ],
                'allowed' => [
                    'Melihat mahasiswa PA dan KRS mahasiswa PA',
                    'Approve/reject KRS',
                    'Memberikan catatan akademik dan menindaklanjuti alert',
                    'Mengelola jadwal konsultasi',
                ],
                'denied' => [
                    'Tidak approve KRS mahasiswa di luar tanggung jawab',
                    'Tidak mengubah nilai mahasiswa atau penjadwalan fakultas',
                ],
            ],
            'dosen_pembimbing' => [
                'label' => 'Dosen Pembimbing',
                'panel' => '{tenant}.sifakueu.test/dosen',
                'scope' => 'Mahasiswa bimbingan',
                'menus' => [
                    'Dashboard',
                    'Profil Saya',
                    'Jadwal Mengajar',
                    'Mahasiswa Bimbingan',
                    'Dokumen TA: Review Bab, Komentar, Approve Bab, Log Revisi',
                    'Bimbingan: Jadwal Konsultasi, Riwayat Bimbingan, Progress TA',
                    'Profil Keahlian',
                    'Riwayat Mengajar',
                    'Notifikasi',
                ],
                'allowed' => [
                    'Melihat mahasiswa bimbingan dan dokumen TA bimbingan',
                    'Memberikan komentar dan approve/reject bab',
                    'Melihat log revisi dan progress TA',
                    'Mengatur jadwal konsultasi',
                ],
                'denied' => [
                    'Tidak membuka dokumen mahasiswa yang bukan bimbingannya',
                    'Tidak mengubah dokumen mahasiswa',
                    'Tidak menetapkan dirinya sendiri sebagai penguji',
                ],
            ],
            'dosen_penguji' => [
                'label' => 'Dosen Penguji',
                'panel' => '{tenant}.sifakueu.test/dosen',
                'scope' => 'Sidang ditugaskan',
                'menus' => [
                    'Dashboard',
                    'Profil Saya',
                    'Jadwal Mengajar',
                    'Jadwal Menguji',
                    'Detail Sidang',
                    'Penilaian Sidang',
                    'Catatan/Revisi',
                    'Riwayat Menguji',
                    'Notifikasi',
                ],
                'allowed' => [
                    'Melihat sidang yang ditugaskan',
                    'Melihat dokumen peserta sidang terkait',
                    'Input nilai, catatan, dan revisi',
                    'Melihat jadwal dan riwayat menguji',
                ],
                'denied' => [
                    'Tidak melihat sidang yang tidak ditugaskan',
                    'Tidak mengubah jadwal sendiri, pembimbing, atau nilai penguji lain',
                ],
            ],
            'kaprodi' => [
                'label' => 'Kaprodi',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Prodi',
                'menus' => [
                    'Dashboard Prodi',
                    'Monitoring Mahasiswa',
                    'Sidang',
                    'Profil Dosen',
                    'Profil Mahasiswa',
                    'CPL/PLO',
                    'Alert',
                    'Approval',
                    'Dokumen TA',
                    'Laporan Prodi',
                ],
                'allowed' => [
                    'Melihat dashboard dan monitoring prodi',
                    'Menyetujui proses Kaprodi',
                    'Validasi plotting dosen dan bidang/rumpun',
                    'Melihat CPL/PLO, profiling, laporan, dan status dokumen TA',
                ],
                'denied' => [
                    'Tidak mengelola Super Admin atau tenant lain',
                    'Tidak menghapus tenant',
                ],
            ],
            'dekan' => [
                'label' => 'Dekan',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Fakultas',
                'menus' => [
                    'Dashboard Fakultas',
                    'Monitoring Fakultas',
                    'Approval Surat',
                    'Profil Dosen',
                    'Profil Mahasiswa',
                    'CPL/PLO',
                    'Laporan Fakultas',
                    'Audit Akademik Ringkas',
                ],
                'allowed' => [
                    'Melihat dashboard dan monitoring fakultas',
                    'Approval surat strategis',
                    'Melihat data agregat dosen/mahasiswa, CPL/PLO, dan laporan fakultas',
                ],
                'denied' => [
                    'Tidak mengakses tenant lain atau membuat tenant baru',
                    'Tidak mengubah data operasional tanpa permission',
                ],
            ],
            'wd' => [
                'label' => 'Wakil Dekan',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Fakultas',
                'menus' => [
                    'Dashboard Fakultas',
                    'Monitoring Fakultas',
                    'Approval Surat',
                    'Profil Dosen',
                    'Profil Mahasiswa',
                    'CPL/PLO',
                    'Laporan Fakultas',
                    'Audit Akademik Ringkas',
                ],
                'allowed' => [
                    'Melihat dashboard dan monitoring fakultas',
                    'Approval surat strategis',
                    'Melihat data agregat dosen/mahasiswa, CPL/PLO, dan laporan fakultas',
                ],
                'denied' => [
                    'Tidak mengakses tenant lain atau membuat tenant baru',
                    'Tidak mengubah data operasional tanpa permission',
                ],
            ],
            'kbk' => [
                'label' => 'KBK',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'KBK',
                'menus' => [
                    'Dashboard KBK',
                    'Rumpun Ilmu',
                    'Keahlian Dosen',
                    'Profil Dosen',
                    'Matriks Kesesuaian',
                    'Rekomendasi Pengampu',
                    'Gap Kompetensi',
                ],
                'allowed' => [
                    'Melihat dosen sesuai scope KBK',
                    'Validasi rumpun ilmu dan keahlian',
                    'Melihat matriks kesesuaian, rekomendasi dosen, dan gap kompetensi',
                ],
                'denied' => [
                    'Tidak mengubah nilai, approve KRS, atau mengelola tenant',
                ],
            ],
            'lpm' => [
                'label' => 'LPM / Gugus Mutu',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Mutu fakultas',
                'menus' => [
                    'Dashboard Mutu',
                    'CPL',
                    'PLO',
                    'Monitoring Capaian',
                    'Gap CPL',
                    'Profil Lulusan',
                    'Repositori TA',
                    'Laporan Akreditasi',
                    'Audit Data',
                ],
                'allowed' => [
                    'Melihat CPL/PLO, tren capaian, gap, dan data agregat mahasiswa',
                    'Mengakses repositori TA sesuai kebutuhan akreditasi',
                    'Menghasilkan laporan mutu/akreditasi',
                ],
                'denied' => [
                    'Tidak mengubah nilai, approve KRS, menetapkan penguji, atau mengubah dokumen TA',
                ],
            ],
            'baak' => [
                'label' => 'BAAK',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Akademik tenant',
                'menus' => [
                    'Dashboard Akademik',
                    'Rekap Mahasiswa',
                    'Rekap Sidang',
                    'Monitoring',
                    'Yudisium',
                    'Dokumen TA',
                    'Laporan Akademik',
                ],
                'allowed' => [
                    'Melihat rekap akademik dan sidang',
                    'Melihat status kelulusan, dokumen final, dan yudisium',
                    'Membuat laporan akademik sesuai scope',
                ],
                'denied' => [
                    'Tidak mengelola dosen, rekomendasi, tenant, atau data tenant lain',
                ],
            ],
            'kepala_laboratorium' => [
                'label' => 'Kepala Laboratorium',
                'panel' => '{tenant}.sifakueu.test/pimpinan',
                'scope' => 'Laboratorium',
                'menus' => [
                    'Dashboard Laboratorium',
                    'Profil Dosen',
                    'Profil Mahasiswa',
                    'Kebutuhan Asisten',
                    'Topik Riset',
                    'Rekomendasi Mahasiswa',
                    'Laporan Laboratorium',
                ],
                'allowed' => [
                    'Melihat data dosen dan mahasiswa yang relevan',
                    'Melihat rekomendasi kompetensi',
                    'Mengelola kebutuhan asisten dan topik riset/laboratorium',
                    'Melihat laporan laboratorium',
                ],
                'denied' => [
                    'Tidak melihat seluruh data mahasiswa tanpa scope',
                    'Tidak approve KRS, mengubah nilai, atau mengelola tenant',
                ],
            ],
            'alumni' => [
                'label' => 'Alumni / Pengguna Lulusan',
                'panel' => 'Portal terbatas',
                'scope' => 'Diri sendiri',
                'menus' => [
                    'Form Feedback Alumni',
                    'Form Feedback Pengguna Lulusan',
                    'Survey Kompetensi',
                    'Riwayat Feedback Sendiri',
                ],
                'allowed' => [
                    'Memberikan feedback',
                    'Mengisi survey',
                    'Melihat submission milik sendiri jika akun digunakan',
                ],
                'denied' => [
                    'Tidak mengakses panel administrasi atau data internal mahasiswa',
                ],
            ],
        ];
    }
}
