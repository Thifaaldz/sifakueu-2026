<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiPengampu extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mata_kuliah_id',
        'semester_id',
        'dosen_id',
        'ranking',
        'score',
        'score_breakdown',
        'summary_reason',
        'status',
        'justification',
        'reviewed_by',
        'reviewed_at',
        'generated_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'score_breakdown' => 'array',
        'reviewed_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
