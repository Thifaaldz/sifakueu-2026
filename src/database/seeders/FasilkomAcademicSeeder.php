<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\Cpl;
use App\Models\GraduateProfile;
use App\Models\JenisSurat;
use App\Models\Keahlian;
use App\Models\Kbk;
use App\Models\Kurikulum;
use App\Models\LetterFormField;
use App\Models\LetterTemplate;
use App\Models\MataKuliah;
use App\Models\NotificationTemplate;
use App\Models\PenawaranMataKuliah;
use App\Models\PemetaanCplPlo;
use App\Models\PemetaanMkCpl;
use App\Models\Plo;
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
use App\Services\Sifak\MonitoringRuleService;
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

        $this->seedM6Master($tenant, $kurikulum, [$mkPengantar, $mkAnalisis]);

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
            ['SIDANG_REGISTRATION_SUBMITTED', 'Pendaftaran Sidang Dikirim', 'Pendaftaran sidang {{jenis}} telah dikirim dan menunggu verifikasi.'],
            ['SIDANG_REGISTRATION_VERIFIED', 'Pendaftaran Sidang Terverifikasi', 'Pendaftaran sidang {{jenis}} telah diverifikasi.'],
            ['SIDANG_REGISTRATION_REVISION_REQUIRED', 'Perbaikan Berkas Sidang', 'Pendaftaran sidang membutuhkan perbaikan: {{catatan}}.'],
            ['SIDANG_RESULT_PUBLISHED', 'Hasil Sidang Dipublish', 'Hasil sidang {{jenis}} telah tersedia.'],
            ['SIDANG_REVISION_DEADLINE', 'Deadline Revisi Sidang', 'Revisi sidang memiliki deadline {{deadline}}.'],
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
            ['MONITORING_ALERT_CREATED', 'Alert Monitoring Baru', 'Alert monitoring {{judul}} dibuat dengan status risiko {{risiko}}.'],
            ['MONITORING_ALERT_ACKNOWLEDGED', 'Alert Monitoring Diakui', 'Alert monitoring {{judul}} telah diakui.'],
            ['MONITORING_ALERT_FOLLOWUP', 'Follow-up Monitoring', 'Alert monitoring {{judul}} memiliki tindak lanjut: {{catatan}}.'],
            ['MONITORING_ALERT_ESCALATED', 'Alert Monitoring Dieskalasi', 'Alert monitoring {{judul}} dieskalasi ke {{role}}.'],
            ['MONITORING_ALERT_RESOLVED', 'Alert Monitoring Selesai', 'Alert monitoring {{judul}} telah diselesaikan.'],
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

        $studentLetterFlow = ApprovalFlow::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'FLOW-SURAT-MHS'],
            [
                'name' => 'Flow Surat Mahasiswa',
                'description' => 'Verifikasi admin prodi, approval kaprodi, finalisasi admin fakultas.',
                'active' => true,
            ]
        );

        collect([
            [1, 'admin_prodi', 'VERIFICATION'],
            [2, 'kaprodi', 'APPROVAL'],
            [3, 'admin_fakultas', 'ACKNOWLEDGEMENT'],
        ])->each(function (array $step) use ($tenant, $studentLetterFlow) {
            ApprovalFlowStep::updateOrCreate(
                ['tenant_id' => $tenant->id, 'approval_flow_id' => $studentLetterFlow->id, 'step_order' => $step[0]],
                [
                    'role_code' => $step[1],
                    'approval_type' => $step[2],
                    'required' => true,
                    'can_reject' => true,
                    'can_request_revision' => true,
                ]
            );
        });

        $aktifKuliah = JenisSurat::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'AKTIF-KULIAH'],
            [
                'name' => 'Surat Keterangan Aktif Kuliah',
                'description' => 'Surat keterangan status aktif mahasiswa untuk kebutuhan administrasi.',
                'requester_type' => 'STUDENT',
                'requires_attachment' => false,
                'requires_number' => true,
                'number_pattern' => '{sequence}/AKD/FASILKOM/{bulan_romawi}/{tahun}',
                'approval_flow_id' => $studentLetterFlow->id,
                'approval_flow' => ['admin_prodi', 'kaprodi', 'admin_fakultas'],
                'merge_fields' => ['nama_mahasiswa', 'nim', 'program_studi', 'semester', 'keperluan'],
                'template_body' => 'Surat keterangan aktif kuliah untuk {{ nama }} / {{ nim }}.',
                'is_active' => true,
            ]
        );

        collect([
            ['nama_mahasiswa', 'Nama Mahasiswa', 'TEXT', true, 1],
            ['nim', 'NIM', 'TEXT', true, 2],
            ['program_studi', 'Program Studi', 'TEXT', true, 3],
            ['semester', 'Semester', 'NUMBER', true, 4],
            ['keperluan', 'Keperluan', 'TEXTAREA', true, 5],
        ])->each(function (array $field) use ($tenant, $aktifKuliah) {
            LetterFormField::updateOrCreate(
                ['tenant_id' => $tenant->id, 'jenis_surat_id' => $aktifKuliah->id, 'field_key' => $field[0]],
                [
                    'label' => $field[1],
                    'field_type' => $field[2],
                    'required' => $field[3],
                    'sequence' => $field[4],
                    'active' => true,
                ]
            );
        });

        LetterTemplate::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'TPL-AKTIF-KULIAH', 'version' => '1.0'],
            [
                'jenis_surat_id' => $aktifKuliah->id,
                'name' => 'Template Surat Aktif Kuliah',
                'template_format' => 'HTML',
                'content' => '<h2>Surat Keterangan Aktif Kuliah</h2><p>Nomor: {{ nomor_surat }}</p><p>Yang bertanda tangan di bawah ini menerangkan bahwa {{ nama_mahasiswa }} ({{ nim }}) dari {{ program_studi }} semester {{ semester }} adalah mahasiswa aktif.</p><p>Keperluan: {{ keperluan }}</p><p>Ditetapkan pada {{ tanggal }}.</p>',
                'active' => true,
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
        app(MonitoringRuleService::class)->ensureDefaults($tenant->id);

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

    private function seedM6Master(Tenant $tenant, Kurikulum $kurikulum, array $mataKuliahs): void
    {
        $cplTeknis = Cpl::updateOrCreate(
            ['tenant_id' => $tenant->id, 'kurikulum_id' => $kurikulum->id, 'code' => 'CPL-TEK'],
            ['name' => 'Kemampuan Rekayasa Perangkat Lunak', 'description' => 'Mampu merancang, membangun, dan menguji perangkat lunak.', 'category' => 'KETERAMPILAN_KHUSUS', 'active' => true]
        );

        $cplAnalisis = Cpl::updateOrCreate(
            ['tenant_id' => $tenant->id, 'kurikulum_id' => $kurikulum->id, 'code' => 'CPL-ANL'],
            ['name' => 'Kemampuan Analisis Sistem', 'description' => 'Mampu menganalisis proses bisnis dan kebutuhan sistem.', 'category' => 'PENGETAHUAN', 'active' => true]
        );

        $ploEngineer = Plo::updateOrCreate(
            ['tenant_id' => $tenant->id, 'kurikulum_id' => $kurikulum->id, 'code' => 'PLO-SE'],
            ['name' => 'Software Engineer', 'description' => 'Profil lulusan pengembang perangkat lunak.', 'active' => true]
        );

        $ploAnalyst = Plo::updateOrCreate(
            ['tenant_id' => $tenant->id, 'kurikulum_id' => $kurikulum->id, 'code' => 'PLO-SA'],
            ['name' => 'System Analyst', 'description' => 'Profil lulusan analis sistem dan proses bisnis.', 'active' => true]
        );

        foreach ($mataKuliahs as $index => $mataKuliah) {
            PemetaanMkCpl::updateOrCreate(
                ['tenant_id' => $tenant->id, 'mata_kuliah_id' => $mataKuliah->id, 'cpl_id' => $index === 0 ? $cplAnalisis->id : $cplTeknis->id],
                ['weight' => 1]
            );
        }

        foreach ([[$cplTeknis, $ploEngineer, 0.7], [$cplAnalisis, $ploEngineer, 0.3], [$cplAnalisis, $ploAnalyst, 0.8], [$cplTeknis, $ploAnalyst, 0.2]] as $row) {
            PemetaanCplPlo::updateOrCreate(
                ['tenant_id' => $tenant->id, 'cpl_id' => $row[0]->id, 'plo_id' => $row[1]->id],
                ['weight' => $row[2]]
            );
        }

        $softwareEngineer = GraduateProfile::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'GP-SE'],
            ['program_studi_id' => $kurikulum->program_studi_id, 'name' => 'Software Engineer', 'description' => 'Mengembangkan aplikasi dan solusi perangkat lunak.', 'active' => true]
        );

        $systemAnalyst = GraduateProfile::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'GP-SA'],
            ['program_studi_id' => $kurikulum->program_studi_id, 'name' => 'System Analyst', 'description' => 'Menganalisis kebutuhan bisnis dan sistem informasi.', 'active' => true]
        );

        $softwareEngineer->plos()->syncWithoutDetaching([$ploEngineer->id => ['tenant_id' => $tenant->id, 'weight' => 1]]);
        $systemAnalyst->plos()->syncWithoutDetaching([$ploAnalyst->id => ['tenant_id' => $tenant->id, 'weight' => 1]]);
    }
}
