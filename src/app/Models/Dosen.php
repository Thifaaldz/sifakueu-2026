<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'program_studi_id',
        'rumpun_ilmu_id',
        'kbk_id',
        'nidn',
        'name',
        'email',
        'academic_position',
        'last_education',
        'education',
        'skills',
        'certifications',
        'publications',
        'industry_experience',
        'teaching_load_sks',
        'guidance_load',
        'examiner_load',
        'status',
    ];

    protected $casts = [
        'education' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'publications' => 'array',
        'industry_experience' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function rumpunIlmu(): BelongsTo
    {
        return $this->belongsTo(RumpunIlmu::class);
    }

    public function kbk(): BelongsTo
    {
        return $this->belongsTo(Kbk::class);
    }

    public function jadwalKonsultasis(): HasMany
    {
        return $this->hasMany(JadwalKonsultasi::class);
    }

    public function profil(): HasOne
    {
        return $this->hasOne(DosenProfil::class);
    }

    public function keahlians()
    {
        return $this->belongsToMany(Keahlian::class, 'dosen_keahlian')
            ->withPivot(['tenant_id', 'level', 'evidence_source', 'validation_status', 'validated_by', 'validated_at'])
            ->withTimestamps();
    }

    public function pendidikans(): HasMany
    {
        return $this->hasMany(DosenPendidikan::class);
    }

    public function sertifikasis(): HasMany
    {
        return $this->hasMany(DosenSertifikasi::class);
    }

    public function publikasis(): HasMany
    {
        return $this->hasMany(DosenPublikasi::class);
    }

    public function pengalamanIndustris(): HasMany
    {
        return $this->hasMany(DosenPengalamanIndustri::class);
    }

    public function riwayatMengajars(): HasMany
    {
        return $this->hasMany(RiwayatMengajar::class);
    }

    public function preferensiMks(): HasMany
    {
        return $this->hasMany(DosenPreferensiMk::class);
    }

    public function bebanDosens(): HasMany
    {
        return $this->hasMany(BebanDosen::class);
    }

    public function rekomendasiPengampus(): HasMany
    {
        return $this->hasMany(RekomendasiPengampu::class);
    }

    public function ledProgramStudis(): HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'kaprodi_dosen_id');
    }

    public function ledKbks(): HasMany
    {
        return $this->hasMany(Kbk::class, 'ketua_dosen_id');
    }

    public function tugasAkhirBimbinganUtama(): HasMany
    {
        return $this->hasMany(TugasAkhir::class, 'pembimbing_1_id');
    }

    public function tugasAkhirBimbinganPendamping(): HasMany
    {
        return $this->hasMany(TugasAkhir::class, 'pembimbing_2_id');
    }

    public function sidangAssignments(): HasMany
    {
        return $this->hasMany(SidangAssignment::class);
    }
}
