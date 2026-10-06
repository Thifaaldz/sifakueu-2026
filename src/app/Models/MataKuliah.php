<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataKuliah extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'program_studi_id',
        'rumpun_ilmu_id',
        'code',
        'name',
        'sks',
        'semester',
        'type',
        'prerequisite_course_ids',
        'status',
    ];

    protected $casts = [
        'prerequisite_course_ids' => 'array',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function rumpunIlmu(): BelongsTo
    {
        return $this->belongsTo(RumpunIlmu::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'mata_kuliah_prasyarats', 'mata_kuliah_id', 'prasyarat_mata_kuliah_id')
            ->withPivot(['tenant_id', 'minimum_grade'])
            ->withPivotValue('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())
            ->withTimestamps();
    }

    public function kurikulums(): BelongsToMany
    {
        return $this->belongsToMany(Kurikulum::class, 'kurikulum_mata_kuliah')
            ->withPivot(['tenant_id', 'semester', 'is_required'])
            ->withPivotValue('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())
            ->withTimestamps();
    }

    public function riwayatMengajars()
    {
        return $this->hasMany(RiwayatMengajar::class);
    }

    public function rekomendasiPengampus()
    {
        return $this->hasMany(RekomendasiPengampu::class);
    }

    public function penawaranMataKuliahs()
    {
        return $this->hasMany(PenawaranMataKuliah::class);
    }
}
