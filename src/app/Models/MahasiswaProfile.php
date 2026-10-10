<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'academic_score', 'competency_score', 'profile_status', 'strengths', 'gaps', 'summary_payload', 'last_recalculated_at'];

    protected $casts = [
        'academic_score' => 'decimal:2',
        'competency_score' => 'decimal:2',
        'strengths' => 'array',
        'gaps' => 'array',
        'summary_payload' => 'array',
        'last_recalculated_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
