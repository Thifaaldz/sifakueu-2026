<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyGap extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'competency_type', 'competency_reference_id', 'current_score', 'target_score', 'gap_score', 'severity', 'payload'];

    protected $casts = ['current_score' => 'decimal:2', 'target_score' => 'decimal:2', 'gap_score' => 'decimal:2', 'payload' => 'array'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
