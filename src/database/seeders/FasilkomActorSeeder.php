<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\BebanDosen;
use App\Models\DosenLokasi;
use App\Models\DosenPendidikan;
use App\Models\DosenPengalamanIndustri;
use App\Models\DosenPreferensiMk;
use App\Models\DosenProfil;
use App\Models\DosenPublikasi;
use App\Models\DosenSertifikasi;
use App\Models\JadwalKonsultasi;
use App\Models\JadwalKuliah;
use App\Models\Keahlian;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Kbk;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\PenawaranMataKuliah;
use App\Models\PlottingDosen;
use App\Models\ProgramStudi;
use App\Models\RepositoryItem;
use App\Models\Ruangan;
use App\Models\RiwayatMengajar;
use App\Models\RumpunIlmu;
use App\Models\Semester;
use App\Models\Security\Role;
use App\Models\StoredFile;
use App\Models\TaApproval;
use App\Models\TaDocument;
use App\Models\TaDocumentVersion;
use App\Models\Tenant;
use App\Models\TugasAkhir;
use App\Models\User;
use App\Services\Sifak\DosenRecommendationService;
use App\Services\Sifak\TaProgressService;
use App\Services\Sifak\TaService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class FasilkomActorSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'fasilkom')->firstOrFail();
        app(TenantContext::class)->set($tenant);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $programStudi = ProgramStudi::query()->where('code', 'IF')->firstOrFail();
        $rumpunIlmu = RumpunIlmu::query()->where('name', 'Rekayasa Perangkat Lunak')->firstOrFail();
        $kbk = Kbk::query()->where('name', 'Sistem Informasi')->firstOrFail();

        $this->removeSplitLecturerAccounts($tenant);

        $this->seedUser($tenant, 'Admin FASILKOM', 'admin.fasilkom@sifak.local', 'admin_fakultas');
        $this->seedUser($tenant, 'Admin Prodi Informatika', 'admin.prodi.fasilkom@sifak.local', 'admin_prodi');
        $this->seedUser($tenant, 'BAAK FASILKOM', 'baak.fasilkom@sifak.local', 'baak');
        $this->seedUser($tenant, 'LPM FASILKOM', 'lpm.fasilkom@sifak.local', 'lpm');
        $this->seedUser($tenant, 'Dekan FASILKOM', 'dekan.fasilkom@sifak.local', 'dekan');
        $this->seedUser($tenant, 'Wakil Dekan FASILKOM', 'wd.fasilkom@sifak.local', 'wd');

        $this->seedLecturer($tenant, $programStudi, $rumpunIlmu, $kbk, [
            'name' => 'Kaprodi Informatika',
            'email' => 'kaprodi.fasilkom@sifak.local',
            'role' => 'kaprodi',
            'nidn' => '0011223301',
            'position' => 'Kepala Program Studi',
        ]);

        $this->seedLecturer($tenant, $programStudi, $rumpunIlmu, $kbk, [
            'name' => 'Koordinator KBK Sistem Informasi',
            'email' => 'kbk.fasilkom@sifak.local',
            'role' => 'kbk',
            'nidn' => '0011223302',
            'position' => 'Koordinator KBK',
        ]);

        $this->seedLecturer($tenant, $programStudi, $rumpunIlmu, $kbk, [
            'name' => 'Kepala Laboratorium Komputasi',
            'email' => 'kalab.fasilkom@sifak.local',
            'role' => 'kepala_laboratorium',
            'nidn' => '0011223303',
            'position' => 'Kepala Laboratorium',
        ]);

        $this->seedLecturer($tenant, $programStudi, $rumpunIlmu, $kbk, [
            'name' => 'Dr. Dosen FASILKOM',
            'email' => 'dosen.fasilkom@sifak.local',
            'roles' => ['dosen', 'dosen_pa', 'dosen_pembimbing', 'dosen_penguji'],
            'nidn' => '0011223343',
            'position' => 'Dosen Tetap / PA / Pembimbing / Penguji',
        ]);

        $this->seedStudent($tenant, $programStudi, [
            'name' => 'Mahasiswa FASILKOM',
            'email' => 'mahasiswa.fasilkom@sifak.local',
            'nim' => '2026001001',
        ]);

        $this->seedStudent($tenant, $programStudi, [
            'name' => 'Alumni FASILKOM',
            'email' => 'alumni.fasilkom@sifak.local',
            'nim' => '2020001001',
            'role' => 'alumni',
            'semester' => 8,
            'status' => 'lulus',
        ]);

        $semester = Semester::query()->where('code', '2026-GANJIL')->first();
        MataKuliah::query()
            ->whereIn('code', ['IF101', 'SI201'])
            ->get()
            ->each(fn (MataKuliah $mataKuliah) => app(DosenRecommendationService::class)->refreshForCourse($mataKuliah, 5, $semester));

        $this->seedM4Example($tenant);
        $this->seedM7Example($tenant);

        app(TenantContext::class)->clear();
    }

    private function seedM4Example(Tenant $tenant): void
    {
        $semester = Semester::query()->where('code', '2026-GANJIL')->first();
        $mahasiswa = Mahasiswa::query()->where('nim', '2026001001')->first();
        $dosen = Dosen::query()->where('nidn', '0011223343')->first();
        $ruangan = Ruangan::query()->where('code', 'R-301')->first();

        if (! $semester || ! $mahasiswa || ! $dosen || ! $ruangan) {
            return;
        }

        $offerings = PenawaranMataKuliah::query()
            ->with('mataKuliah')
            ->where('semester_id', $semester->id)
            ->get();

        if ($offerings->isEmpty()) {
            return;
        }

        $krs = Krs::updateOrCreate(
            ['tenant_id' => $tenant->id, 'mahasiswa_id' => $mahasiswa->id, 'semester_id' => $semester->id],
            [
                'tahun_akademik_id' => $semester->tahun_akademik_id,
                'dosen_pa_id' => $dosen->id,
                'mata_kuliah_id' => $offerings->first()->mata_kuliah_id,
                'academic_year' => '2026/2027',
                'term' => 'Ganjil',
                'status' => 'waiting_pa',
                'submitted_at' => now(),
                'total_sks' => $offerings->sum(fn (PenawaranMataKuliah $offering) => $offering->mataKuliah?->sks ?? 0),
            ]
        );

        $offerings->values()->each(function (PenawaranMataKuliah $offering, int $index) use ($tenant, $krs, $dosen, $semester, $ruangan) {
            $kelas = KelasKuliah::updateOrCreate(
                ['tenant_id' => $tenant->id, 'penawaran_mata_kuliah_id' => $offering->id, 'kode_kelas' => $offering->mataKuliah->code . '-A'],
                [
                    'kapasitas' => 35,
                    'jumlah_peserta' => 1,
                    'status' => 'scheduled',
                ]
            );

            KrsDetail::updateOrCreate(
                ['tenant_id' => $tenant->id, 'krs_id' => $krs->id, 'mata_kuliah_id' => $offering->mata_kuliah_id],
                [
                    'penawaran_mata_kuliah_id' => $offering->id,
                    'kelas_kuliah_id' => $kelas->id,
                    'sks' => $offering->mataKuliah?->sks ?? 0,
                    'status' => 'selected',
                    'validation_status' => 'valid',
                ]
            );

            PlottingDosen::updateOrCreate(
                ['tenant_id' => $tenant->id, 'kelas_kuliah_id' => $kelas->id, 'dosen_id' => $dosen->id, 'role_pengampu' => 'utama'],
                [
                    'sks_beban' => $offering->mataKuliah?->sks ?? 0,
                    'status' => 'assigned',
                    'justification' => 'Seeder M4 FASILKOM.',
                ]
            );

            JadwalKuliah::updateOrCreate(
                ['tenant_id' => $tenant->id, 'kelas_kuliah_id' => $kelas->id],
                [
                    'semester_id' => $semester->id,
                    'mata_kuliah_id' => $offering->mata_kuliah_id,
                    'dosen_id' => $dosen->id,
                    'ruangan_id' => $ruangan->id,
                    'academic_year' => '2026/2027',
                    'term' => 'Ganjil',
                    'day_of_week' => 1 + $index,
                    'starts_at' => $index === 0 ? '08:00:00' : '10:00:00',
                    'ends_at' => $index === 0 ? '09:40:00' : '11:40:00',
                    'minggu_mulai' => 1,
                    'minggu_selesai' => 16,
                    'mode' => 'onsite',
                    'status' => 'planned',
                    'conflict_status' => 'unchecked',
                ]
            );

            $offering->update(['jumlah_peminat' => 1, 'target_jumlah_kelas' => 1]);
        });
    }

    /**
     * @param string|array<int, string> $roles
     */
    private function seedUser(Tenant $tenant, string $name, string $email, string|array $roles): User
    {
        $roles = (array) $roles;

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name' => $name,
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        $user->syncRoles($roles);

        return $user;
    }

    /**
     * @param array{name:string,email:string,role?:string,roles?:array<int, string>,nidn:string,position:string} $actor
     */
    private function seedLecturer(
        Tenant $tenant,
        ProgramStudi $programStudi,
        RumpunIlmu $rumpunIlmu,
        Kbk $kbk,
        array $actor
    ): void {
        $user = $this->seedUser($tenant, $actor['name'], $actor['email'], $actor['roles'] ?? $actor['role']);

        $dosen = Dosen::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nidn' => $actor['nidn']],
            [
                'user_id' => $user->id,
                'program_studi_id' => $programStudi->id,
                'rumpun_ilmu_id' => $rumpunIlmu->id,
                'kbk_id' => $kbk->id,
                'name' => $actor['name'],
                'academic_position' => $actor['position'],
                'education' => ['S2 Sistem Informasi'],
                'skills' => ['Laravel', 'Sistem Informasi', 'Software Engineering'],
                'certifications' => ['Web Development'],
                'publications' => ['Academic information system integration'],
                'teaching_load_sks' => 6,
                'guidance_load' => 4,
                'examiner_load' => 2,
                'status' => 'active',
            ]
        );

        $this->seedM5ForLecturer($tenant, $dosen, $actor);
    }

    /**
     * @param array{name:string,email:string,role?:string,roles?:array<int, string>,nidn:string,position:string} $actor
     */
    private function seedM5ForLecturer(Tenant $tenant, Dosen $dosen, array $actor): void
    {
        $semester = Semester::query()->where('code', '2026-GANJIL')->first();
        $mkPengantar = MataKuliah::query()->where('code', 'IF101')->first();
        $mkAnalisis = MataKuliah::query()->where('code', 'SI201')->first();

        DosenProfil::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id],
            [
                'profile_summary' => $actor['name'] . ' berfokus pada sistem informasi akademik, rekayasa perangkat lunak, dan pembelajaran berbasis proyek.',
                'expertise_focus' => 'Software Engineering; Information System; Academic System',
                'industry_experience_summary' => 'Pengalaman pendampingan implementasi aplikasi akademik dan otomasi proses bisnis kampus.',
                'profile_status' => 'verified',
                'verified_by' => $dosen->user_id,
                'verified_at' => now(),
            ]
        );

        $expertiseCodes = $actor['email'] === 'kalab.fasilkom@sifak.local'
            ? ['RPL-WEB', 'DATA-DB']
            : ['RPL-SE', 'SI-BIS', 'RPL-WEB'];

        Keahlian::query()
            ->whereIn('code', $expertiseCodes)
            ->get()
            ->each(fn (Keahlian $keahlian) => $dosen->keahlians()->syncWithoutDetaching([
                $keahlian->id => [
                    'tenant_id' => $tenant->id,
                    'level' => 'advanced',
                    'evidence_source' => 'Seeder M5 FASILKOM',
                    'validation_status' => 'validated',
                    'validated_by' => $dosen->user_id,
                    'validated_at' => now(),
                ],
            ]));

        DosenPendidikan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'degree' => 'S2'],
            [
                'institution' => 'Universitas Esa Unggul',
                'study_program' => 'Sistem Informasi',
                'graduation_year' => 2018,
                'field' => 'Software Engineering',
            ]
        );

        DosenSertifikasi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'certificate_number' => 'CERT-' . $actor['nidn']],
            [
                'name' => 'Certified Web Application Developer',
                'issuer' => 'SIFAK Academy',
                'field' => 'Software Engineering',
                'issued_on' => '2025-02-01',
                'expires_on' => '2028-02-01',
                'validation_status' => 'validated',
            ]
        );

        DosenPublikasi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'title' => 'Model Integrasi Sistem Informasi Akademik Multi-Tenant'],
            [
                'year' => 2026,
                'type' => 'jurnal',
                'publisher' => 'Jurnal Sistem Informasi Akademik',
                'field' => 'Information System',
                'keywords' => ['academic system', 'multi tenant', 'software engineering'],
                'source' => 'manual',
            ]
        );

        DosenPengalamanIndustri::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'institution' => 'Pusat Sistem Informasi Kampus'],
            [
                'position' => 'System Analyst',
                'field' => 'Information System',
                'starts_on' => '2022-01-01',
                'ends_on' => '2025-12-31',
                'description' => 'Menganalisis proses akademik dan merancang integrasi layanan administrasi fakultas.',
            ]
        );

        foreach ([$mkPengantar, $mkAnalisis] as $index => $mataKuliah) {
            if (! $mataKuliah) {
                continue;
            }

            RiwayatMengajar::updateOrCreate(
                ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'mata_kuliah_id' => $mataKuliah->id, 'semester_id' => $semester?->id],
                [
                    'tahun_akademik_id' => $semester?->tahun_akademik_id,
                    'sks' => $mataKuliah->sks,
                    'class_name' => chr(65 + $index),
                    'average_evaluation' => 4.35,
                    'student_count' => 32,
                    'status' => 'completed',
                ]
            );

            DosenPreferensiMk::updateOrCreate(
                ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'mata_kuliah_id' => $mataKuliah->id, 'semester_id' => $semester?->id],
                [
                    'preference_level' => $index === 0 ? 5 : 4,
                    'notes' => 'Sesuai fokus keahlian dan pengalaman mengajar.',
                ]
            );
        }

        BebanDosen::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'semester_id' => $semester?->id],
            [
                'teaching_sks' => 6,
                'guidance_count' => 4,
                'examiner_count' => 2,
                'research_load' => 2,
                'workload_score' => 9,
                'workload_status' => 'normal',
                'calculated_at' => now(),
            ]
        );

        JadwalKonsultasi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id, 'day_of_week' => 1, 'starts_at' => '09:00:00'],
            [
                'ends_at' => '11:00:00',
                'room' => 'Ruang Dosen FASILKOM',
                'type' => 'offline',
                'is_active' => true,
            ]
        );

        DosenLokasi::updateOrCreate(
            ['tenant_id' => $tenant->id, 'dosen_id' => $dosen->id],
            [
                'latitude' => -6.190000,
                'longitude' => 106.760000,
                'accuracy' => 25,
                'presence_status' => 'available',
                'recorded_at' => now(),
            ]
        );
    }

    private function removeSplitLecturerAccounts(Tenant $tenant): void
    {
        $emails = [
            'dosen.umum.fasilkom@sifak.local',
            'dosen.pa.fasilkom@sifak.local',
            'dosen.pembimbing.fasilkom@sifak.local',
            'dosen.penguji.fasilkom@sifak.local',
        ];

        $nidns = [
            '0011223344',
            '0011223345',
            '0011223346',
        ];

        Dosen::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('nidn', $nidns)
            ->update([
                'user_id' => null,
                'status' => 'inactive',
            ]);

        User::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('email', $emails)
            ->delete();
    }

    private function seedM7Example(Tenant $tenant): void
    {
        $semester = Semester::query()->where('code', '2026-GANJIL')->first();
        $mahasiswa = Mahasiswa::query()->where('nim', '2026001001')->first();
        $dosen = Dosen::query()->where('nidn', '0011223343')->first();

        if (! $semester || ! $mahasiswa || ! $dosen) {
            return;
        }

        $ta = app(TaService::class)->createForStudent($mahasiswa, [
            'judul' => 'Rancang Bangun Sistem Informasi Akademik Fakultas Berbasis Multi-Tenant',
            'judul_en' => 'Design and Implementation of a Multi-Tenant Faculty Academic Information System',
            'topik' => 'Sistem informasi akademik, manajemen tugas akhir, dan repositori digital.',
            'keywords' => ['sifak', 'multi tenant', 'tugas akhir', 'repository'],
            'pembimbing_1_id' => $dosen->id,
            'tahun_akademik_id' => $semester->tahun_akademik_id,
            'semester_id' => $semester->id,
            'status' => 'active',
        ]);

        $ta->documents()->with('section')->get()->each(function (TaDocument $document) use ($tenant, $ta, $mahasiswa, $dosen) {
            $sectionCode = $document->section?->code ?? 'DOKUMEN';
            $content = 'Seed dokumen TA ' . $sectionCode . ' untuk ' . $mahasiswa->nim . '.';
            $checksum = hash('sha256', $content);

            $storedFile = StoredFile::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'module' => 'ta',
                    'owner_type' => TaDocument::class,
                    'owner_id' => (string) $document->id,
                    'original_name' => strtolower($sectionCode) . '.pdf',
                    'version' => 1,
                ],
                [
                    'stored_name' => strtolower($sectionCode) . '_' . $mahasiswa->nim . '.pdf',
                    'disk' => 'local',
                    'path' => 'seed/m7/' . $mahasiswa->nim . '/' . strtolower($sectionCode) . '.pdf',
                    'mime_type' => 'application/pdf',
                    'size' => strlen($content),
                    'checksum' => $checksum,
                    'status' => 'active',
                    'uploaded_by' => $mahasiswa->user_id,
                ]
            );

            $version = TaDocumentVersion::updateOrCreate(
                ['tenant_id' => $tenant->id, 'ta_document_id' => $document->id, 'version_number' => 1],
                [
                    'stored_file_id' => $storedFile->id,
                    'submitted_by' => $mahasiswa->user_id,
                    'submitted_at' => now(),
                    'change_summary' => 'Seeder dokumen awal ' . $sectionCode,
                    'status' => 'reviewed',
                    'checksum' => $checksum,
                ]
            );

            $document->update([
                'current_version_id' => $version->id,
                'status' => 'approved',
                'approved_by' => $dosen->id,
                'approved_at' => now(),
            ]);

            TaApproval::updateOrCreate(
                ['tenant_id' => $tenant->id, 'ta_document_id' => $document->id, 'approval_type' => 'section'],
                [
                    'approver_id' => $dosen->id,
                    'status' => 'approved',
                    'note' => 'Seeder approval pembimbing.',
                    'approved_at' => now(),
                ]
            );
        });

        $ta = app(TaProgressService::class)->recalculate($ta->fresh(), 'Seeder M7 FASILKOM.');
        $firstDocument = $ta->documents()->orderBy('id')->first();

        if (! $firstDocument) {
            return;
        }

        $finalContent = 'Dokumen final seed TA ' . $mahasiswa->nim . '.';
        $finalChecksum = hash('sha256', $finalContent);
        $finalFile = StoredFile::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'module' => 'ta',
                'owner_type' => TugasAkhir::class,
                'owner_id' => (string) $ta->id,
                'original_name' => 'thesis_final_fasilkom.pdf',
                'version' => 1,
            ],
            [
                'stored_name' => 'thesis_final_' . $mahasiswa->nim . '.pdf',
                'disk' => 'local',
                'path' => 'seed/m7/' . $mahasiswa->nim . '/final/thesis_final.pdf',
                'mime_type' => 'application/pdf',
                'size' => strlen($finalContent),
                'checksum' => $finalChecksum,
                'status' => 'active',
                'uploaded_by' => $mahasiswa->user_id,
            ]
        );

        $finalVersion = TaDocumentVersion::updateOrCreate(
            ['tenant_id' => $tenant->id, 'ta_document_id' => $firstDocument->id, 'version_number' => 2],
            [
                'stored_file_id' => $finalFile->id,
                'submitted_by' => $mahasiswa->user_id,
                'submitted_at' => now(),
                'change_summary' => 'Seeder dokumen final terkompilasi.',
                'status' => 'reviewed',
                'checksum' => $finalChecksum,
            ]
        );

        $ta->update([
            'final_document_version_id' => $finalVersion->id,
            'status' => 'finalized',
            'progress_percent' => 100,
        ]);

        RepositoryItem::updateOrCreate(
            ['tenant_id' => $tenant->id, 'tugas_akhir_id' => $ta->id],
            [
                'final_file_id' => $finalFile->id,
                'title' => $ta->judul,
                'abstract_id' => 'Repositori seed untuk pengujian alur M7 dokumen TA.',
                'abstract_en' => 'Seed repository item for testing M7 thesis document workflow.',
                'keywords' => $ta->keywords,
                'author_name' => $mahasiswa->name,
                'nim' => $mahasiswa->nim,
                'program_studi_id' => $mahasiswa->program_studi_id,
                'supervisor_names' => [$dosen->name],
                'year' => 2026,
                'access_level' => config('sifak_m7.repository.default_access_level', 'internal'),
                'status' => 'published',
                'published_at' => now(),
            ]
        );
    }

    /**
     * @param array{name:string,email:string,nim:string,role?:string,semester?:int,status?:string} $actor
     */
    private function seedStudent(Tenant $tenant, ProgramStudi $programStudi, array $actor): void
    {
        $role = $actor['role'] ?? 'mahasiswa';
        $user = $this->seedUser($tenant, $actor['name'], $actor['email'], $role);

        Mahasiswa::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nim' => $actor['nim']],
            [
                'user_id' => $user->id,
                'program_studi_id' => $programStudi->id,
                'name' => $actor['name'],
                'angkatan' => (int) substr($actor['nim'], 0, 4),
                'semester' => $actor['semester'] ?? 1,
                'ipk' => 3.50,
                'sks_lulus' => $role === 'alumni' ? 144 : 0,
                'status' => $actor['status'] ?? 'active',
                'interests' => ['software engineering', 'data'],
                'profile_payload' => ['seed_actor' => $role],
            ]
        );
    }
}
