<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidangRequirement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sidang_type_id',
        'code',
        'name',
        'requirement_type',
        'required',
        'source_module',
        'validation_rule',
        'sequence',
        'active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'active' => 'boolean',
        'validation_rule' => 'array',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(SidangType::class, 'sidang_type_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SidangRequirementResult::class);
    }
}
