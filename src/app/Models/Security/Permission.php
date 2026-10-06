<?php

namespace App\Models\Security;

use App\Support\Tenancy\TenantContext;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function getConnectionName(): ?string
    {
        return app(TenantContext::class)->get()?->database_name ? 'tenant' : parent::getConnectionName();
    }
}
