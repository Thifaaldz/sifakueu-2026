<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cpl extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'kurikulum_id', 'code', 'name', 'description', 'category', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function mataKuliahMappings(): HasMany
    {
        return $this->hasMany(PemetaanMkCpl::class);
    }

    public function ploMappings(): HasMany
    {
        return $this->hasMany(PemetaanCplPlo::class);
    }
}
