<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidangRubric extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_type_id', 'code', 'name', 'weight', 'min_score', 'max_score', 'sequence', 'active'];

    protected $casts = [
        'weight' => 'decimal:2',
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(SidangType::class, 'sidang_type_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SidangScore::class);
    }
}
