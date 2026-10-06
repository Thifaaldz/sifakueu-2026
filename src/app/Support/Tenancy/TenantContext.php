<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;

        if ($tenant?->database_name) {
            config([
                'database.connections.tenant.database' => $tenant->database_name,
                'database.connections.tenant.driver' => config('database.connections.' . config('database.default') . '.driver', config('database.default')),
                'database.connections.tenant.host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
                'database.connections.tenant.port' => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
                'database.connections.tenant.username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
                'database.connections.tenant.password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
                'permission.cache.key' => 'spatie.permission.cache.tenant.' . $tenant->slug,
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
        config(['permission.cache.key' => 'spatie.permission.cache.central']);
        DB::purge('tenant');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
