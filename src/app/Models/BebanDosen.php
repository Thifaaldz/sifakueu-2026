<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BebanDosen extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'semester_id', 'teaching_sks', 'guidance_count', 'examiner_count', 'research_load', 'workload_score', 'workload_status', 'calculated_at'];

    protected $casts = [
        'workload_score' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
