<?php

namespace App\Services\Shared\Scheduler;

use App\Models\ScheduledTaskLog;
use App\Models\Tenant;
use App\Services\Shared\Audit\AuditService;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Support\Facades\Log;

class TenantSchedulerService
{
    public function __construct(private readonly AuditService $audit) {}

    public function runForActiveTenants(string $taskName, Closure $task): void
    {
        Tenant::query()
            ->where('status', 'active')
            ->orderBy('slug')
            ->each(function (Tenant $tenant) use ($taskName, $task) {
                $context = app(TenantContext::class);
                $context->set($tenant);

                $log = ScheduledTaskLog::create([
                    'tenant_id' => $tenant->id,
                    'task_name' => $taskName,
                    'started_at' => now(),
                    'status' => 'processing',
                ]);

                try {
                    $result = $task($tenant);

                    $log->update([
                        'finished_at' => now(),
                        'status' => 'completed',
                        'message' => is_string($result) ? $result : 'Task completed.',
                    ]);

                    $this->audit->record('SCHEDULER_RUN', 'SHARED', ScheduledTaskLog::class, [], $log->fresh()->toArray());
                } catch (\Throwable $exception) {
                    $log->update([
                        'finished_at' => now(),
                        'status' => 'failed',
                        'message' => $exception->getMessage(),
                    ]);

                    Log::error('SIFAK tenant scheduled task failed', [
                        'tenant' => $tenant->slug,
                        'task' => $taskName,
                        'exception' => $exception,
                    ]);
                } finally {
                    $context->clear();
                }
            });
    }
}
