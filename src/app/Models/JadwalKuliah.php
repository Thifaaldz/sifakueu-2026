<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKuliah extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'semester_id',
        'kelas_kuliah_id',
        'mata_kuliah_id',
        'dosen_id',
        'ruangan_id',
        'academic_year',
        'term',
        'day_of_week',
        'starts_at',
        'ends_at',
        'minggu_mulai',
        'minggu_selesai',
        'mode',
        'status',
        'created_by',
        'updated_by',
        'finalized_at',
        'conflict_status',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(JadwalConflict::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(JadwalHistory::class);
    }
}
