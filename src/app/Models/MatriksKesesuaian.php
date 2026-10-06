<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriksKesesuaian extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'dosen_id',
        'mata_kuliah_id',
        'rumpun_score',
        'history_score',
        'publication_score',
        'certification_score',
        'preference_score',
        'overload_penalty',
        'final_score',
        'score_breakdown',
        'justification',
        'semester_id',
        'generated_at',
    ];

    protected $casts = [
        'score_breakdown' => 'array',
        'generated_at' => 'datetime',
        'final_score' => 'decimal:2',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
