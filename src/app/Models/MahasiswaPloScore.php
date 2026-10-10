<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaPloScore extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'plo_id', 'score', 'rank_order', 'calculated_at', 'payload'];

    protected $casts = ['score' => 'decimal:2', 'calculated_at' => 'datetime', 'payload' => 'array'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function plo(): BelongsTo
    {
        return $this->belongsTo(Plo::class);
    }
}
