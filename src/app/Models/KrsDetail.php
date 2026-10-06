<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KrsDetail extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'krs_id',
        'mata_kuliah_id',
        'penawaran_mata_kuliah_id',
        'kelas_kuliah_id',
        'sks',
        'status',
        'validation_status',
        'validation_note',
    ];

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function penawaranMataKuliah(): BelongsTo
    {
        return $this->belongsTo(PenawaranMataKuliah::class);
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class);
    }

    public function validationResults(): HasMany
    {
        return $this->hasMany(KrsValidationResult::class);
    }
}
