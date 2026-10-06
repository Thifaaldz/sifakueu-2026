<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SifakHosts extends Command
{
    protected $signature = 'sifak:hosts
        {--ip=127.0.0.1 : IP address for local host entries}
        {--plain : Print only host lines}';

    protected $description = 'Generate local /etc/hosts entries for SIFAK tenants.';

    public function handle(): int
    {
        if (! Schema::hasTable('tenants')) {
            $this->error('Tabel tenants belum tersedia. Jalankan migration terlebih dahulu.');

            return self::FAILURE;
        }

        $ip = (string) $this->option('ip');
        $baseDomain = config('sifak.base_domain');

        $hosts = collect([$baseDomain, 'admin.' . $baseDomain])
            ->merge(Tenant::query()
                ->whereNotNull('slug')
                ->orderBy('slug')
                ->pluck('slug')
                ->map(fn (string $slug) => $slug . '.' . $baseDomain))
            ->filter()
            ->unique()
            ->values();

        if ($this->option('plain')) {
            $hosts->each(fn (string $host) => $this->line($ip . ' ' . $host));

            return self::SUCCESS;
        }

        $this->info('Tambahkan entry berikut ke /etc/hosts laptop/host:');
        $this->newLine();

        $hosts->each(fn (string $host) => $this->line($ip . ' ' . $host));

        $this->newLine();
        $this->comment('Jika memakai Docker, jalankan dari root project: ./scripts/sifak-sync-hosts.sh');

        return self::SUCCESS;
    }
}
