<?php

namespace App\Services\Sifak;

use App\Models\Tenant;
use App\Models\TenantAuditLog;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantProvisioningService
{
    public function provision(array $data, ?User $actor = null): Tenant
    {
        $slug = $this->normalizeSlug($data['slug'] ?? $data['code'] ?? $data['name']);
        $this->validateSlug($slug);
        $databaseName = $this->databaseName($slug);

        $tenant = DB::transaction(function () use ($data, $actor, $slug, $databaseName) {
            return Tenant::create([
                'name' => $data['name'],
                'code' => Str::upper($data['code'] ?? $slug),
                'slug' => $slug,
                'subdomain' => $this->subdomain($slug),
                'database_name' => $databaseName,
                'database_connection' => 'tenant',
                'status' => $data['status'] ?? 'active',
                'enabled_modules' => array_keys(config('sifak.modules')),
                'settings' => $data['settings'] ?? [],
                'created_by' => $actor?->id,
                'provisioned_at' => now(),
            ]);
        });

        $this->provisionDatabase($tenant, $data);

        TenantAuditLog::create([
            'tenant_id' => $tenant->id,
            'actor_id' => $actor?->id,
            'action' => 'tenant.provisioned',
            'description' => 'Tenant ' . $tenant->name . ' berhasil diprovisioning.',
            'properties' => [
                'modules' => $tenant->enabled_modules,
                'subdomain' => $tenant->subdomain,
                'database_name' => $tenant->database_name,
            ],
        ]);

        return $tenant;
    }

    public function normalizeSlug(string $value): string
    {
        return Str::slug(Str::lower($value));
    }

    public function validateSlug(string $slug, ?int $ignoreTenantId = null): void
    {
        $errors = [];

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $slug)) {
            $errors['slug'] = 'Slug tenant harus berupa subdomain valid.';
        }

        if (in_array($slug, config('sifak.reserved_subdomains'), true)) {
            $errors['slug'] = 'Slug tenant termasuk reserved subdomain.';
        }

        $exists = Tenant::where('slug', $slug)
            ->when($ignoreTenantId, fn ($query) => $query->whereKeyNot($ignoreTenantId))
            ->exists();

        if ($exists) {
            $errors['slug'] = 'Slug tenant sudah digunakan.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function subdomain(string $slug): string
    {
        return $slug . '.' . config('sifak.base_domain');
    }

    public function databaseName(string $slug): string
    {
        $prefix = Str::slug(env('PROJECT_NAME', env('DB_DATABASE', 'sifakueu')), '_');

        return Str::limit($prefix . '_tenant_' . Str::slug($slug, '_'), 64, '');
    }

    public function provisionDatabase(Tenant $tenant, array $data = []): void
    {
        $databaseName = $tenant->database_name ?: $this->databaseName($tenant->slug);

        DB::statement('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $databaseName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $this->configureTenantConnection($databaseName);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->seedTenantShell($tenant, $data);
    }

    public function configureTenantConnection(string $databaseName): void
    {
        config([
            'database.connections.tenant.database' => $databaseName,
            'database.connections.tenant.driver' => config('database.connections.' . config('database.default') . '.driver', config('database.default')),
            'database.connections.tenant.host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'database.connections.tenant.port' => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'database.connections.tenant.username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
            'database.connections.tenant.password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function seedTenantShell(Tenant $tenant, array $data): void
    {
        DB::connection('tenant')->table('tenants')->updateOrInsert(
            ['id' => $tenant->id],
            [
                'uuid' => $tenant->uuid,
                'name' => $tenant->name,
                'code' => $tenant->code,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'database_name' => $tenant->database_name,
                'database_connection' => 'tenant',
                'status' => $tenant->status,
                'enabled_modules' => json_encode($tenant->enabled_modules),
                'settings' => json_encode($tenant->settings ?? []),
                'created_by' => null,
                'provisioned_at' => $tenant->provisioned_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $roleNames = [
            'admin_fakultas',
            'admin_prodi',
            'mahasiswa',
            'dosen_pembimbing',
            'dosen_penguji',
            'dosen_pa',
            'kaprodi',
            'dekan',
            'wd',
            'kbk',
            'lpm',
            'kepala_laboratorium',
            'alumni',
            'baak',
        ];

        foreach ($roleNames as $roleName) {
            DB::connection('tenant')->table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        app(TenantContext::class)->set($tenant);
        app(RoleSeeder::class)->run();
        app(TenantContext::class)->clear();

        if (! empty($data['admin_email'])) {
            $this->createTenantAdmin($tenant, [
                'name' => $data['admin_name'] ?? 'Admin ' . $tenant->code,
                'email' => $data['admin_email'],
                'password' => $data['admin_password'] ?? 'password',
            ]);
        }
    }

    public function createTenantAdmin(Tenant $tenant, array $data): void
    {
        $this->configureTenantConnection($tenant->database_name ?: $this->databaseName($tenant->slug));

        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => $data['email']],
            [
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $adminUserId = DB::connection('tenant')->table('users')
            ->where('email', $data['email'])
            ->value('id');

        $adminRoleId = DB::connection('tenant')->table('roles')
            ->where('name', 'admin_fakultas')
            ->where('guard_name', 'web')
            ->value('id');

        if ($adminUserId && $adminRoleId) {
            DB::connection('tenant')->table('model_has_roles')->updateOrInsert([
                'role_id' => $adminRoleId,
                'model_type' => User::class,
                'model_id' => $adminUserId,
            ]);
        }
    }
}
