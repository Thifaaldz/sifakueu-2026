<?php

namespace App\Jobs\Shared;

use App\Models\SifakNotification;
use App\Models\Tenant;
use App\Services\Shared\Notification\NotificationService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 180, 300];

    public function __construct(
        public readonly int $tenantId,
        public readonly int $notificationId,
    ) {}

    public function handle(NotificationService $notifications): void
    {
        $context = app(TenantContext::class);
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        $context->set($tenant);

        try {
            $notification = SifakNotification::query()->findOrFail($this->notificationId);
            $notifications->send($notification);
        } finally {
            $context->clear();
        }
    }

    public function failed(\Throwable $exception): void
    {
        $context = app(TenantContext::class);
        $tenant = Tenant::query()->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $context->set($tenant);

        try {
            SifakNotification::query()
                ->whereKey($this->notificationId)
                ->update([
                    'status' => 'failed',
                    'failed_reason' => $exception->getMessage(),
                ]);
        } finally {
            $context->clear();
        }
    }
}
