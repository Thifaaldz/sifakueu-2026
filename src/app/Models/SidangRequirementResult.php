<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangRequirementResult extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sidang_registration_id',
        'sidang_requirement_id',
        'status',
        'source_reference_type',
        'source_reference_id',
        'note',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(SidangRequirement::class, 'sidang_requirement_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
