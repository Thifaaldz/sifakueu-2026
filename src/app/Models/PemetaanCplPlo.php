<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemetaanCplPlo extends Model
{
    use BelongsToTenant;

    protected $table = 'pemetaan_cpl_plos';

    protected $fillable = ['tenant_id', 'cpl_id', 'plo_id', 'weight'];

    protected $casts = ['weight' => 'decimal:2'];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function plo(): BelongsTo
    {
        return $this->belongsTo(Plo::class);
    }
}
