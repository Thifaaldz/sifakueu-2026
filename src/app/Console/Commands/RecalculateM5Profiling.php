<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\ScheduledTaskLog;
use App\Models\Semester;
use App\Models\Tenant;
use App\Services\Sifak\DosenRecommendationService;
use App\Services\Sifak\DosenWorkloadService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecalculateM5Profiling extends Command
{
    protected $signature = 'sifak:m5:recalculate
        {--slug= : Tenant slug tertentu}
        {--semester= : Kode semester, contoh 2026-GANJIL}
        {--limit=5 : Jumlah rekomendasi per mata kuliah}';

    protected $description = 'Recalculate Modul 5 dosen workload, matching matrix, and teaching recommendations.';

    public function handle(DosenWorkloadService $workloads, DosenRecommendationService $recommendations): int
    {
        $query = Tenant::query()->where('status', 'active')->orderBy('slug');

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant aktif yang cocok.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->info('M5 recalculation: ' . $tenant->slug);
            $this->runForTenant($tenant, $workloads, $recommendations);
        }

        $this->info('M5 recalculation completed.');

        return self::SUCCESS;
    }

    private function runForTenant(Tenant $tenant, DosenWorkloadService $workloads, DosenRecommendationService $recommendations): void
    {
        $context = app(TenantContext::class);
        $context->set($tenant);

        $log = ScheduledTaskLog::create([
            'tenant_id' => $tenant->id,
            'task_name' => 'm5-recalculate',
            'started_at' => now(),
            'status' => 'processing',
        ]);

        try {
            $semester = $this->option('semester')
                ? Semester::query()->where('code', $this->option('semester'))->first()
                : Semester::query()->where('status', 'active')->latest('starts_on')->first();

            $dosenCount = 0;
            Dosen::query()
                ->where('status', 'active')
                ->each(function (Dosen $dosen) use ($workloads, $semester, &$dosenCount) {
                    $workloads->calculate($dosen, $semester);
                    $dosenCount++;
                });

            $courseCount = 0;
            MataKuliah::query()
                ->where('status', 'active')
                ->each(function (MataKuliah $mataKuliah) use ($recommendations, $semester, &$courseCount) {
                    $recommendations->refreshForCourse($mataKuliah, (int) $this->option('limit'), $semester);
                    $courseCount++;
                });

            $log->update([
                'finished_at' => now(),
                'status' => 'completed',
                'message' => "Calculated {$dosenCount} dosen workload(s) and {$courseCount} course recommendation set(s).",
            ]);
        } catch (\Throwable $exception) {
            $log->update([
                'finished_at' => now(),
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);

            Log::error('SIFAK M5 recalculation failed', [
                'tenant' => $tenant->slug,
                'exception' => $exception,
            ]);
        } finally {
            $context->clear();
        }
    }
}
