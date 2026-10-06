<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Models\StoredFile;
use App\Services\Shared\Scheduler\TenantSchedulerService;
use App\Services\Sifak\AcademicAlertEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunSharedScheduler extends Command
{
    protected $signature = 'sifak:shared:scheduler
        {task : cleanup-temp-files|evaluate-alerts}
        {--days=7 : Minimum age in days for cleanup-temp-files}';

    protected $description = 'Run tenant-aware shared service scheduled tasks.';

    public function handle(TenantSchedulerService $scheduler, AcademicAlertEngine $alerts): int
    {
        $task = (string) $this->argument('task');

        match ($task) {
            'cleanup-temp-files' => $scheduler->runForActiveTenants($task, function () {
                $days = (int) $this->option('days');
                $files = StoredFile::query()
                    ->where('status', 'temp')
                    ->where('created_at', '<', now()->subDays($days))
                    ->get();

                foreach ($files as $file) {
                    Storage::disk($file->disk)->delete($file->path);
                    $file->delete();
                }

                return $files->count() . ' temporary file(s) cleaned.';
            }),
            'evaluate-alerts' => $scheduler->runForActiveTenants($task, function () use ($alerts) {
                $count = 0;

                Mahasiswa::query()
                    ->where('status', 'active')
                    ->each(function (Mahasiswa $mahasiswa) use ($alerts, &$count) {
                        $alerts->evaluate($mahasiswa);
                        $count++;
                    });

                return $count . ' student alert profile(s) evaluated.';
            }),
            default => throw new \InvalidArgumentException('UNKNOWN_SHARED_SCHEDULER_TASK'),
        };

        $this->info('Shared scheduler task completed: ' . $task);

        return self::SUCCESS;
    }
}
