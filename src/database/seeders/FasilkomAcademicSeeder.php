<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\JenisSurat;
use App\Models\Keahlian;
use App\Models\Kbk;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\NotificationTemplate;
use App\Models\PenawaranMataKuliah;
use App\Models\PeriodeKrs;
use App\Models\ProgramStudi;
use App\Models\Ruangan;
use App\Models\RumpunIlmu;
use App\Models\Semester;
use App\Models\SidangRequirement;
use App\Models\SidangRubric;
use App\Models\SidangType;
use App\Models\TahunAkademik;
use App\Models\TemplateDokumen;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class FasilkomAcademicSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'fasilkom')->firstOrFail();
        app(TenantContext::class)->set($tenant);

        $fakultas = Fakultas::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'FASILKOM'],
            [
                'name' => 'Fakultas Ilmu Komputer',
                'short_name' => 'FASILKOM',
                'email' => 'fasilkom@sifak.local',
                'phone' => '021-000000',
                'address' => 'Kampus SIFAK',
                'status' => 'active',
            ]
        );

        $tahunAkademik = TahunAkademik::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => '2026/2027'],
            [
                'start_year' => 2026,
                'end_year' => 2027,
                'status' => 'active',
            ]
        );

        $semesterAktif = Semester::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => '2026-GANJIL'],
            [
                'tahun_akademik_id' => $tahunAkademik->id,
                'name' => 'Ganjil',
                'starts_on' => '2026-09-01',
                'ends_on' => '2027-01-31',
                'status' => 'active',
            ]
        );

        $informatika = ProgramStudi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'IF'],
            ['fakultas_id' => $fakultas->id, 'name' => 'Informatika', 'degree' => 'S1', 'status' => 'active']
        );

        $sistemInformasi = ProgramStudi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SI'],
            ['fakultas_id' => $fakultas->id, 'name' => 'Sistem Informasi', 'degree' => 'S1', 'status' => 'active']
        );

        $rumpun = RumpunIlmu::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Rekayasa Perangkat Lunak'],
            ['code' => 'RPL', 'description' => 'Software engineering, sistem informasi, dan pengembangan aplikasi.', 'status' => 'active']
        );

        Kbk::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Sistem Informasi'],
            ['code' => 'SI', 'field' => 'Software Engineering & Information Systems', 'description' => 'KBK sistem informasi dan rekayasa perangkat lunak.', 'status' => 'active']
        );

        collect([
            ['RPL-SE', 'Software Engineering', 'Rekayasa perangkat lunak, SDLC, arsitektur aplikasi, dan pengujian.'],
            ['RPL-WEB', 'Web Application Development', 'Pengembangan aplikasi web, Laravel, API, dan integrasi sistem.'],
            ['SI-BIS', 'Business Information System', 'Analisis proses bisnis, sistem informasi manajemen, dan enterprise system.'],
            ['DATA-DB', 'Database & Data Engineering', 'Basis data, pemodelan data, dan pipeline data akademik.'],
            ['AI-ML', 'Artificial Intelligence', 'Kecerdasan buatan, machine learning, dan analitik prediktif.'],
        ])->each(function (array $item) use ($tenant, $rumpun) {
            Keahlian::updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $item[0]],
                [
                    'name' => $item[1],
                    'description' => $item[2],
                    'rumpun_ilmu_id' => $rumpun->id,
                    'status' => 'active',
                ]
            );
        });

        $mkPengantar = MataKuliah::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'IF101'],
            [
                'program_studi_id' => $informatika->id,
                'rumpun_ilmu_id' => $rumpun->id,
                'name' => 'Pengantar Sistem Informasi',
                'sks' => 3,
                'semester' => 1,
                'type' => 'wajib',
                'status' => 'active',
            ]
        );

        $mkAnalisis = MataKuliah::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SI201'],
            [
                'program_studi_id' => $sistemInformasi->id,
                'rumpun_ilmu_id' => $rumpun->id,
                'name' => 'Analisis dan Perancangan Sistem',
                'sks' => 3,
                'semester' => 3,
                'type' => 'wajib',
                'status' => 'active',
            ]
        );

        $kurikulum = Kurikulum::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'KUR-IF-2026'],
            [
                'program_studi_id' => $informatika->id,
                'name' => 'Kurikulum Informatika 2026',
                'year' => 2026,
                'start_year' => 2026,
                'end_year' => null,
                'total_sks' => 144,
                'status' => 'active',
                'is_active' => true,
            ]
        );

        $kurikulum->mataKuliahs()->syncWithoutDetaching([
            $mkPengantar->id => ['tenant_id' => $tenant->id, 'semester' => 1, 'is_required' => true],
            $mkAnalisis->id => ['tenant_id' => $tenant->id, 'semester' => 3, 'is_required' => true],
        ]);

        PeriodeKrs::updateOrCreate(
            ['tenant_id' => $tenant->id, 'semester_id' => $semesterAktif->id, 'program_studi_id' => $informatika->id],
            [
                'tanggal_mulai' => '2026-09-01 00:00:00',
                'tanggal_selesai' => '2026-09-30 23:59:59',
                'tanggal_revisi_mulai' => '2026-10-01 00:00:00',
                'tanggal_revisi_selesai' => '2026-10-07 23:59:59',
                'status' => 'open',
            ]
        );

        foreach ([$mkPengantar, $mkAnalisis] as $mataKuliah) {
            PenawaranMataKuliah::updateOrCreate(
                ['tenant_id' => $tenant->id, 'semester_id' => $semesterAktif->id, 'program_studi_id' => $informatika->id, 'mata_kuliah_id' => $mataKuliah->id],
                [
                    'kurikulum_id' => $kurikulum->id,
                    'kuota_default' => 35,
                    'minimal_peserta' => 10,
                    'maksimal_peserta' => 35,
                    'target_jumlah_kelas' => 1,
                    'status' => 'open',
                ]
            );
        }

        collect([
            ['SIDANG_SCHEDULED', 'Jadwal Sidang Telah Ditetapkan', 'Sidang Anda telah dijadwalkan pada {{tanggal}} pukul {{waktu}} di ruang {{ruang}}.'],
            ['SURAT_APPROVED', 'Surat Disetujui', 'Pengajuan surat {{nomor_pengajuan}} telah disetujui.'],
            ['KRS_APPROVED', 'KRS Disetujui', 'KRS periode {{periode}} telah disetujui oleh dosen PA.'],
            ['KRS_REJECTED', 'KRS Perlu Revisi', 'KRS periode {{periode}} ditolak dengan catatan: {{catatan}}.'],
            ['ALERT_CREATED', 'Alert Akademik Baru', 'Alert {{judul}} dibuat untuk profil akademik Anda.'],
            ['TA_REVIEWED', 'Dokumen TA Direview', 'Dokumen TA {{bagian}} telah diberi komentar oleh pembimbing.'],
            ['TA_APPROVED', 'Dokumen TA Disetujui', 'Dokumen TA {{bagian}} telah disetujui.'],
            ['TA_DOCUMENT_SUBMITTED', 'Dokumen TA Menunggu Review', 'Dokumen TA {{bagian}} telah dikirim untuk review pembimbing.'],
            ['TA_REVISION_REQUIRED', 'Revisi Dokumen TA', 'Dokumen TA {{bagian}} membutuhkan revisi: {{catatan}}.'],
            ['TA_SECTION_APPROVED', 'Bagian TA Disetujui', 'Bagian {{bagian}} telah disetujui pembimbing.'],
            ['TA_READY_FOR_FINALIZATION', 'TA Siap Finalisasi', 'Seluruh bagian wajib TA telah disetujui dan siap finalisasi.'],
            ['TA_FINAL_DOCUMENT_READY', 'Dokumen Final TA Siap', 'Dokumen final TA telah dikompilasi dan siap masuk repositori.'],
            ['TA_REVISION_DEADLINE', 'Batas Revisi TA', 'Batas revisi dokumen TA adalah {{deadline}}.'],
        ])->each(function (array $template) use ($tenant) {
            NotificationTemplate::updateOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $template[0]],
                [
                    'title' => $template[1],
                    'body' => $template[2],
                    'available_channels' => ['in_app', 'email'],
                    'status' => 'active',
                ]
            );
        });

        Ruangan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'LAB-1'],
            ['name' => 'Laboratorium Komputasi 1', 'capacity' => 40, 'type' => 'lab', 'location' => 'Gedung FASILKOM Lt. 2', 'building' => 'Gedung FASILKOM', 'floor' => '2', 'status' => 'active']
        );

        Ruangan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'R-301'],
            ['name' => 'Ruang Kuliah 301', 'capacity' => 45, 'type' => 'kelas', 'location' => 'Gedung FASILKOM Lt. 3', 'building' => 'Gedung FASILKOM', 'floor' => '3', 'status' => 'active']
        );

        JenisSurat::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'AKTIF-KULIAH'],
            [
                'name' => 'Surat Keterangan Aktif Kuliah',
                'approval_flow' => ['Admin Prodi', 'Admin Fakultas'],
                'merge_fields' => ['nama', 'nim', 'prodi', 'semester'],
                'template_body' => 'Surat keterangan aktif kuliah untuk {{ nama }} / {{ nim }}.',
                'is_active' => true,
            ]
        );

        TemplateDokumen::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Template TA FASILKOM', 'version' => '2026.1'],
            [
                'program_studi_id' => $informatika->id,
                'citation_style' => 'APA',
                'format_rules' => ['font' => 'Times New Roman', 'size' => 12, 'margin' => '4-4-3-3'],
                'is_active' => true,
            ]
        );

        $this->seedSidangMaster($tenant);

        app(TenantContext::class)->clear();
    }

    private function seedSidangMaster(Tenant $tenant): void
    {
        $sempro = SidangType::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SEMPRO'],
            ['name' => 'Seminar Proposal', 'description' => 'Seminar proposal tugas akhir.', 'active' => true]
        );

        $sidangTa = SidangType::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'SIDANG_TA'],
            ['name' => 'Sidang Tugas Akhir', 'description' => 'Sidang akhir tugas akhir.', 'active' => true]
        );

        collect([
            [$sempro, 'MAHASISWA_AKTIF', 'Mahasiswa aktif', 'system', 'master', []],
            [$sempro, 'MIN_SKS', 'Minimal SKS Sempro', 'system', 'master', ['min_sks' => 90]],
            [$sempro, 'PEMBIMBING_VALID', 'Pembimbing valid', 'system', 'M7', []],
            [$sempro, 'BERKAS_ADMIN', 'Berkas administrasi Sempro', 'manual', 'M1', []],
            [$sidangTa, 'MAHASISWA_AKTIF', 'Mahasiswa aktif', 'system', 'master', []],
            [$sidangTa, 'MIN_SKS', 'Minimal SKS Sidang TA', 'system', 'master', ['min_sks' => 120]],
            [$sidangTa, 'TA_READY', 'Dokumen TA lengkap di M7', 'system', 'M7', []],
            [$sidangTa, 'TA_FINAL_DOCUMENT', 'Dokumen final TA tersedia', 'system', 'M7', []],
            [$sidangTa, 'BERKAS_ADMIN', 'Berkas administrasi Sidang TA', 'manual', 'M1', []],
        ])->each(function (array $row, int $index) use ($tenant) {
            SidangRequirement::updateOrCreate(
                ['tenant_id' => $tenant->id, 'sidang_type_id' => $row[0]->id, 'code' => $row[1]],
                [
                    'name' => $row[2],
                    'requirement_type' => $row[3],
                    'required' => true,
                    'source_module' => $row[4],
                    'validation_rule' => $row[5],
                    'sequence' => $index + 1,
                    'active' => true,
                ]
            );
        });

        collect([
            ['MATERI', 'Penguasaan Materi', 30],
            ['METODOLOGI', 'Metodologi', 25],
            ['DOKUMEN', 'Kualitas Dokumen', 20],
            ['PRESENTASI', 'Presentasi', 15],
            ['TANYA_JAWAB', 'Tanya Jawab', 10],
        ])->each(function (array $rubric, int $index) use ($tenant, $sempro, $sidangTa) {
            foreach ([$sempro, $sidangTa] as $type) {
                SidangRubric::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'sidang_type_id' => $type->id, 'code' => $rubric[0]],
                    [
                        'name' => $rubric[1],
                        'weight' => $rubric[2],
                        'min_score' => 0,
                        'max_score' => 100,
                        'sequence' => $index + 1,
                        'active' => true,
                    ]
                );
            }
        });
    }
}
