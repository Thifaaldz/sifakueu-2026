<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keahlian extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'description', 'rumpun_ilmu_id', 'status'];

    public function rumpunIlmu(): BelongsTo
    {
        return $this->belongsTo(RumpunIlmu::class);
    }

    public function dosens(): BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'dosen_keahlian')
            ->withPivot(['tenant_id', 'level', 'evidence_source', 'validation_status', 'validated_by', 'validated_at'])
            ->withTimestamps();
    }
}
