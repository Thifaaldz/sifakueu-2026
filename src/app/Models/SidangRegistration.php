<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SidangRegistration extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sidang_type_id',
        'mahasiswa_id',
        'tugas_akhir_id',
        'semester_id',
        'tahun_akademik_id',
        'registration_number',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(SidangType::class, 'sidang_type_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tugasAkhir(): BelongsTo
    {
        return $this->belongsTo(TugasAkhir::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function requirementResults(): HasMany
    {
        return $this->hasMany(SidangRequirementResult::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SidangFile::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(SidangVerification::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SidangAssignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SidangSchedule::class);
    }

    public function activeSchedule(): HasOne
    {
        return $this->hasOne(SidangSchedule::class)->latestOfMany();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(SidangScore::class);
    }

    public function examinerSummaries(): HasMany
    {
        return $this->hasMany(SidangExaminerSummary::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(SidangResult::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SidangRevision::class);
    }

    public function minute(): HasOne
    {
        return $this->hasOne(SidangMinute::class);
    }
}
