<?php

use App\Models\Tenant;
use App\Services\Shared\Storage\TenantStorageService;
use App\Services\Shared\Workflow\WorkflowService;
use App\Support\Tenancy\TenantContext;

it('validates workflow transitions centrally', function () {
    $workflow = app(WorkflowService::class);

    expect($workflow->can('krs', 'draft', 'diajukan'))->toBeTrue()
        ->and($workflow->can('krs', 'draft', 'final'))->toBeFalse()
        ->and($workflow->can('sidang', 'verifikasi', 'ditolak'))->toBeTrue();
});

it('builds tenant-aware storage paths and rejects traversal', function () {
    app(TenantContext::class)->set(new Tenant(['slug' => 'fasilkom']));

    $storage = app(TenantStorageService::class);

    expect($storage->path('ta', '2026/bab-1.pdf'))->toBe('tenants/fasilkom/ta/2026/bab-1.pdf');

    $storage->path('ta', '../secret.pdf');
})->throws(InvalidArgumentException::class, 'PATH_TRAVERSAL_DETECTED');
