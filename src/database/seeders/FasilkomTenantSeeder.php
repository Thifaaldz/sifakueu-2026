<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Sifak\TenantProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FasilkomTenantSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin SIFAK',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $tenantService = app(TenantProvisioningService::class);

        $tenant = Tenant::query()->where('slug', 'fasilkom')->first()
            ?? $tenantService->provision([
                'name' => 'Fakultas Ilmu Komputer',
                'code' => 'FASILKOM',
                'slug' => 'fasilkom',
                'admin_name' => 'Admin FASILKOM',
                'admin_email' => 'admin.fasilkom@sifak.local',
                'admin_password' => 'password',
                'status' => 'active',
            ], $superAdmin);

        $tenant->forceFill([
            'name' => 'Fakultas Ilmu Komputer',
            'code' => 'FASILKOM',
            'slug' => 'fasilkom',
            'subdomain' => $tenantService->subdomain('fasilkom'),
            'database_name' => $tenant->database_name ?: $tenantService->databaseName('fasilkom'),
            'database_connection' => 'tenant',
            'status' => 'active',
        ])->save();

        $tenantService->provisionDatabase($tenant, [
            'admin_name' => 'Admin FASILKOM',
            'admin_email' => 'admin.fasilkom@sifak.local',
            'admin_password' => 'password',
        ]);
    }
}
