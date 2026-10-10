<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaCplScore extends Model
{
    use BelongsToTenant;

    protected $table = 'skor_cpl_mahasiswas';

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'cpl_id', 'semester_id', 'score', 'semester', 'source', 'calculated_at', 'payload'];

    protected $casts = ['score' => 'decimal:2', 'calculated_at' => 'datetime', 'payload' => 'array'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function semesterModel(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
