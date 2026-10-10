<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRecommendation extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'recommendation_type', 'reference_type', 'reference_id', 'score', 'rank_order', 'reason_summary', 'status', 'payload', 'generated_at'];

    protected $casts = ['score' => 'decimal:2', 'payload' => 'array', 'generated_at' => 'datetime'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
