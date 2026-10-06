<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaProgressLog extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'tugas_akhir_id', 'progress_type', 'old_status', 'new_status', 'progress_percent', 'note', 'actor_id', 'created_at'];

    protected $casts = [
        'progress_percent' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function tugasAkhir(): BelongsTo
    {
        return $this->belongsTo(TugasAkhir::class);
    }
}
