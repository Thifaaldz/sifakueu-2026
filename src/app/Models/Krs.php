<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Krs extends Model
{
    use BelongsToTenant;

    protected $table = 'krs';

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'semester_id',
        'tahun_akademik_id',
        'dosen_pa_id',
        'mata_kuliah_id',
        'approved_by',
        'academic_year',
        'term',
        'total_sks',
        'status',
        'submitted_at',
        'approved_at',
        'finalized_at',
        'finalized_by',
        'validation_notes',
        'note',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'finalized_at' => 'datetime',
        'validation_notes' => 'array',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function dosenPa(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'approved_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(KrsDetail::class);
    }

    public function validationResults(): HasMany
    {
        return $this->hasMany(KrsValidationResult::class);
    }
}
