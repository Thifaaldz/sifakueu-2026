<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenawaranMataKuliah extends Model
{
    use BelongsToTenant;

    protected $table = 'penawaran_mata_kuliah';

    protected $fillable = [
        'tenant_id',
        'semester_id',
        'program_studi_id',
        'mata_kuliah_id',
        'kurikulum_id',
        'kuota_default',
        'minimal_peserta',
        'maksimal_peserta',
        'target_jumlah_kelas',
        'jumlah_peminat',
        'status',
        'created_by',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function kelasKuliahs(): HasMany
    {
        return $this->hasMany(KelasKuliah::class, 'penawaran_mata_kuliah_id');
    }

    public function krsDetails(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'penawaran_mata_kuliah_id');
    }
}
