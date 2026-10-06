<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasKuliah extends Model
{
    use BelongsToTenant;

    protected $table = 'kelas_kuliah';

    protected $fillable = [
        'tenant_id',
        'penawaran_mata_kuliah_id',
        'kode_kelas',
        'kapasitas',
        'jumlah_peserta',
        'status',
        'created_by',
    ];

    public function penawaranMataKuliah(): BelongsTo
    {
        return $this->belongsTo(PenawaranMataKuliah::class);
    }

    public function krsDetails(): HasMany
    {
        return $this->hasMany(KrsDetail::class);
    }

    public function plottingDosens(): HasMany
    {
        return $this->hasMany(PlottingDosen::class);
    }

    public function jadwalKuliahs(): HasMany
    {
        return $this->hasMany(JadwalKuliah::class);
    }
}
