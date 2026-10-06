<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'program_studi_id',
        'nim',
        'name',
        'email',
        'phone',
        'angkatan',
        'semester',
        'ipk',
        'sks_lulus',
        'status',
        'interests',
        'profile_payload',
    ];

    protected $casts = [
        'ipk' => 'decimal:2',
        'interests' => 'array',
        'profile_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class);
    }

    public function dokumenTas(): HasMany
    {
        return $this->hasMany(DokumenTa::class);
    }

    public function tugasAkhirs(): HasMany
    {
        return $this->hasMany(TugasAkhir::class);
    }

    public function sidangRegistrations(): HasMany
    {
        return $this->hasMany(SidangRegistration::class);
    }
}
