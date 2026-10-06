<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Sifak\TenantProvisioningService;
use Illuminate\Console\Command;

class ProvisionTenantDatabases extends Command
{
    protected $signature = 'sifak:tenants:provision-databases {--slug= : Provision one tenant slug only}';

    protected $description = 'Create/migrate tenant databases for database-per-tenant mode.';

    public function handle(TenantProvisioningService $service): int
    {
        $query = Tenant::query()->orderBy('slug');

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant yang ditemukan.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            if (! $tenant->database_name) {
                $tenant->forceFill([
                    'database_name' => $service->databaseName($tenant->slug),
                    'database_connection' => 'tenant',
                ])->save();
            }

            $this->info('Provisioning ' . $tenant->slug . ' -> ' . $tenant->database_name);
            $service->provisionDatabase($tenant);
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
