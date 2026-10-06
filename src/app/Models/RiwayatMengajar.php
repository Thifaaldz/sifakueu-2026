<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatMengajar extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'mata_kuliah_id', 'semester_id', 'tahun_akademik_id', 'sks', 'class_name', 'average_evaluation', 'student_count', 'status'];

    protected $casts = [
        'average_evaluation' => 'decimal:2',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
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
}
