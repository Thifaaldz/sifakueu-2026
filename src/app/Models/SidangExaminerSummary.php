<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangExaminerSummary extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'examiner_id', 'total_score', 'recommendation', 'general_note', 'finalized_at'];

    protected $casts = [
        'total_score' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'examiner_id');
    }
}
