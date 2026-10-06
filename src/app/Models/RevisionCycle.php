<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionCycle extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'tugas_akhir_id', 'source', 'started_at', 'deadline', 'status', 'closed_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'deadline' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function tugasAkhir(): BelongsTo
    {
        return $this->belongsTo(TugasAkhir::class);
    }
}
