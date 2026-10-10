<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function monitoringSnapshots(): HasMany
    {
        return $this->hasMany(MonitoringSnapshot::class);
    }

    public function monitoringOverrides(): HasMany
    {
        return $this->hasMany(MonitoringOverride::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MahasiswaProfile::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(MahasiswaInterest::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(MahasiswaCertification::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(MahasiswaPortfolio::class);
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(MahasiswaOrganization::class);
    }

    public function mbkms(): HasMany
    {
        return $this->hasMany(MahasiswaMbkm::class);
    }

    public function cplScores(): HasMany
    {
        return $this->hasMany(MahasiswaCplScore::class);
    }

    public function ploScores(): HasMany
    {
        return $this->hasMany(MahasiswaPloScore::class);
    }

    public function graduateProfileScores(): HasMany
    {
        return $this->hasMany(MahasiswaGraduateProfileScore::class);
    }

    public function competencyGaps(): HasMany
    {
        return $this->hasMany(CompetencyGap::class);
    }

    public function studentRecommendations(): HasMany
    {
        return $this->hasMany(StudentRecommendation::class);
    }
}
