<?php

namespace App\Services\Shared\Notification;

use App\Jobs\Shared\SendNotificationJob;
use App\Models\NotificationTemplate;
use App\Models\SifakNotification;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationService
{
    public function create(
        User|int $recipient,
        string $type,
        string $title,
        string $message,
        string $channel = 'in_app',
        Model|string|null $reference = null,
        array $payload = []
    ): SifakNotification {
        $tenant = app(TenantContext::class)->get();
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        if (! $tenant) {
            throw new \InvalidArgumentException('TENANT_CONTEXT_MISSING');
        }

        return SifakNotification::create([
            'tenant_id' => $tenant?->id,
            'user_id' => $userId,
            'channel' => $channel,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_type' => $reference instanceof Model ? $reference::class : $reference,
            'reference_id' => $reference instanceof Model ? (string) $reference->getKey() : null,
            'status' => $channel === 'in_app' ? 'sent' : 'pending',
            'sent_at' => $channel === 'in_app' ? now() : null,
            'payload' => $payload,
        ]);
    }

    public function queue(SifakNotification $notification): void
    {
        $notification->update([
            'status' => 'queued',
            'queued_at' => now(),
            'failed_reason' => null,
        ]);

        SendNotificationJob::dispatch($notification->tenant_id, $notification->id);
    }

    public function send(SifakNotification $notification): SifakNotification
    {
        try {
            if ($notification->channel === 'in_app') {
                return $this->markSent($notification);
            }

            // Email/WhatsApp adapters can be plugged in here per tenant configuration.
            Log::info('SIFAK notification adapter placeholder', [
                'tenant_id' => $notification->tenant_id,
                'notification_id' => $notification->id,
                'channel' => $notification->channel,
                'type' => $notification->type,
            ]);

            return $this->markSent($notification);
        } catch (\Throwable $exception) {
            $notification->update([
                'status' => 'failed',
                'failed_reason' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function markRead(SifakNotification $notification, ?Carbon $at = null): SifakNotification
    {
        $notification->update([
            'status' => 'read',
            'read_at' => $at ?? now(),
        ]);

        return $notification;
    }

    public function fromTemplate(
        User|int $recipient,
        string $templateCode,
        array $variables = [],
        string $channel = 'in_app',
        Model|string|null $reference = null
    ): SifakNotification {
        $template = NotificationTemplate::query()
            ->where('code', $templateCode)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->create(
            $recipient,
            $templateCode,
            $this->render($template->title, $variables),
            $this->render($template->body, $variables),
            $channel,
            $reference,
            ['variables' => $variables]
        );
    }

    private function markSent(SifakNotification $notification): SifakNotification
    {
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
            'failed_reason' => null,
        ]);

        return $notification;
    }

    private function render(string $template, array $variables): string
    {
        return Str::of($template)
            ->replaceMatches('/{{\\s*([a-zA-Z0-9_\\.]+)\\s*}}/', fn (array $match) => data_get($variables, $match[1], ''))
            ->value();
    }
}
