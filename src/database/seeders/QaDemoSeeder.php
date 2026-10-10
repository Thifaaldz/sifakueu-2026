<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Security\Role;
use App\Models\Semester;
use App\Models\Tenant;
use App\Models\TugasAkhir;
use App\Models\User;
use App\Services\Sifak\TaService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * Data uji untuk QA end-to-end dan manual guide (tidak dipanggil dari DatabaseSeeder).
 *
 * Mahasiswa demo memakai Kaprodi sebagai pembimbing, sehingga akun dosen.fasilkom
 * dapat berperan sebagai penguji pada alur M1 (pembimbing tidak boleh menjadi penguji).
 *
 * php artisan db:seed --class=QaDemoSeeder
 */
class QaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'fasilkom')->firstOrFail();
        app(TenantContext::class)->set($tenant);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $programStudi = ProgramStudi::query()->where('code', 'IF')->firstOrFail();
        $semester = Semester::query()->where('code', '2026-GANJIL')->first();
        $kaprodi = Dosen::query()->where('name', 'Kaprodi Informatika')->firstOrFail();

        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);

        $user = User::updateOrCreate(
            ['email' => 'mahasiswa.demo@sifak.local'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rina Demo QA',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $user->syncRoles(['mahasiswa']);

        $mahasiswa = Mahasiswa::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nim' => '2026001002'],
            [
                'user_id' => $user->id,
                'program_studi_id' => $programStudi->id,
                'name' => 'Rina Demo QA',
                'email' => 'mahasiswa.demo@sifak.local',
                'angkatan' => 2026,
                'semester' => 7,
                'ipk' => 3.42,
                'sks_lulus' => 110,
                'status' => 'active',
                'interests' => ['data science', 'machine learning'],
                'profile_payload' => ['seed_actor' => 'qa_demo'],
            ]
        );

        if (! TugasAkhir::query()->where('mahasiswa_id', $mahasiswa->id)->exists()) {
            app(TaService::class)->createForStudent($mahasiswa, [
                'judul' => 'Prediksi Risiko Akademik Mahasiswa Menggunakan Machine Learning',
                'judul_en' => 'Student Academic Risk Prediction Using Machine Learning',
                'topik' => 'Machine learning untuk early warning akademik.',
                'keywords' => ['machine learning', 'early warning', 'akademik'],
                'pembimbing_1_id' => $kaprodi->id,
                'tahun_akademik_id' => $semester?->tahun_akademik_id,
                'semester_id' => $semester?->id,
                'status' => 'active',
            ]);
        }

        // Mahasiswa demo kedua untuk alur M7 (dibimbing dosen.fasilkom).
        $dosen = Dosen::query()->where('name', 'Dr. Dosen FASILKOM')->firstOrFail();
        $userBudi = User::updateOrCreate(
            ['email' => 'mahasiswa.demo2@sifak.local'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Budi Demo QA',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $userBudi->syncRoles(['mahasiswa']);

        $budi = Mahasiswa::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nim' => '2026001003'],
            [
                'user_id' => $userBudi->id,
                'program_studi_id' => $programStudi->id,
                'name' => 'Budi Demo QA',
                'email' => 'mahasiswa.demo2@sifak.local',
                'angkatan' => 2026,
                'semester' => 8,
                'ipk' => 3.61,
                'sks_lulus' => 128,
                'status' => 'active',
                'interests' => ['information systems'],
                'profile_payload' => ['seed_actor' => 'qa_demo'],
            ]
        );

        if (! TugasAkhir::query()->where('mahasiswa_id', $budi->id)->exists()) {
            app(TaService::class)->createForStudent($budi, [
                'judul' => 'Sistem Rekomendasi Mata Kuliah Pilihan Berbasis Profil Kompetensi Mahasiswa',
                'judul_en' => 'Elective Course Recommender Based on Student Competency Profile',
                'topik' => 'Sistem rekomendasi akademik.',
                'keywords' => ['recommender system', 'kompetensi', 'kurikulum'],
                'pembimbing_1_id' => $dosen->id,
                'tahun_akademik_id' => $semester?->tahun_akademik_id,
                'semester_id' => $semester?->id,
                'status' => 'active',
            ]);
        }

        app(TenantContext::class)->clear();
    }
}
