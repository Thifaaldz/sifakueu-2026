<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemetaanMkCpl extends Model
{
    use BelongsToTenant;

    protected $table = 'pemetaan_mk_cpls';

    protected $fillable = ['tenant_id', 'mata_kuliah_id', 'cpl_id', 'weight'];

    protected $casts = ['weight' => 'decimal:2'];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }
}
