<?php

namespace App\Services\Shared\Audit;

use App\Models\AuditLog;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'secret',
        'remember_token',
    ];

    public function record(
        string $action,
        ?string $module = null,
        Model|string|null $resource = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null,
        ?Request $request = null,
        array $context = []
    ): AuditLog {
        $tenant = app(TenantContext::class)->get();
        $user = Auth::user();
        $request ??= request();

        return AuditLog::create([
            'tenant_id' => $tenant?->id,
            'user_id' => $userId ?? $user?->id,
            'role' => $context['role'] ?? $user?->roles?->pluck('name')->first(),
            'module' => $module,
            'action' => Str::upper($action),
            'resource_type' => $this->resourceType($resource),
            'resource_id' => $this->resourceId($resource),
            'old_values' => $this->mask($oldValues),
            'new_values' => $this->mask($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_id' => $context['request_id'] ?? $request?->headers->get('X-Request-Id') ?? (string) Str::uuid(),
        ]);
    }

    private function resourceType(Model|string|null $resource): ?string
    {
        return $resource instanceof Model ? $resource::class : $resource;
    }

    private function resourceId(Model|string|null $resource): ?string
    {
        return $resource instanceof Model ? (string) $resource->getKey() : null;
    }

    private function mask(array $values): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            if (Arr::has($values, $key)) {
                Arr::set($values, $key, '[FILTERED]');
            }
        }

        return $values;
    }
}
