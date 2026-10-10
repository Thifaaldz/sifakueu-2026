<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaGraduateProfileScore extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'graduate_profile_id', 'score', 'rank_order', 'generated_at', 'payload'];

    protected $casts = ['score' => 'decimal:2', 'generated_at' => 'datetime', 'payload' => 'array'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function graduateProfile(): BelongsTo
    {
        return $this->belongsTo(GraduateProfile::class);
    }
}
