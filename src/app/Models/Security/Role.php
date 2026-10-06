<?php

namespace App\Models\Security;

use App\Support\Tenancy\TenantContext;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function getConnectionName(): ?string
    {
        return app(TenantContext::class)->get()?->database_name ? 'tenant' : parent::getConnectionName();
    }
}
