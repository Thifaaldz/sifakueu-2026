<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangResult extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'final_score', 'final_grade', 'decision', 'decision_note', 'decided_by', 'decided_at', 'published_at'];

    protected $casts = [
        'final_score' => 'decimal:2',
        'decided_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
