<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangScore extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'examiner_id', 'sidang_rubric_id', 'score', 'note', 'submitted_at'];

    protected $casts = [
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'examiner_id');
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(SidangRubric::class, 'sidang_rubric_id');
    }
}
