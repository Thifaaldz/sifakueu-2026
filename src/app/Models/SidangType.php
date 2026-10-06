<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidangType extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function requirements(): HasMany
    {
        return $this->hasMany(SidangRequirement::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SidangRegistration::class);
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(SidangRubric::class);
    }
}
