<?php

namespace App\Services\Shared\Storage;

use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TenantStorageService
{
    public function disk(?string $disk = null): Filesystem
    {
        return Storage::disk($disk ?: config('filesystems.default'));
    }

    public function path(string $module, string $relativePath = ''): string
    {
        $tenant = app(TenantContext::class)->get();

        if (! $tenant) {
            throw new InvalidArgumentException('TENANT_CONTEXT_MISSING');
        }

        $module = Str::of($module)->lower()->slug('/')->trim('/')->value();
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

        if (str_contains($relativePath, '..')) {
            throw new InvalidArgumentException('PATH_TRAVERSAL_DETECTED');
        }

        return collect(['tenants', $tenant->slug, $module, $relativePath])
            ->filter()
            ->implode('/');
    }

    public function put(string $module, string $relativePath, string $contents, ?string $disk = null): string
    {
        $path = $this->path($module, $relativePath);
        $this->disk($disk)->put($path, $contents);

        return $path;
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        if (str_contains($path, '..') || ! str_starts_with($path, 'tenants/')) {
            throw new InvalidArgumentException('INVALID_TENANT_PATH');
        }

        return $this->disk($disk)->delete($path);
    }
}
