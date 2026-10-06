<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'dosen_id', 'role', 'recommendation_id', 'status', 'assigned_by', 'assigned_at', 'justification'];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
