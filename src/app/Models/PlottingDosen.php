<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlottingDosen extends Model
{
    use BelongsToTenant;

    protected $table = 'plotting_dosen';

    protected $fillable = [
        'tenant_id',
        'kelas_kuliah_id',
        'dosen_id',
        'rekomendasi_pengampu_id',
        'role_pengampu',
        'sks_beban',
        'status',
        'selected_by',
        'justification',
    ];

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function rekomendasiPengampu(): BelongsTo
    {
        return $this->belongsTo(RekomendasiPengampu::class);
    }

    public function selector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }
}
