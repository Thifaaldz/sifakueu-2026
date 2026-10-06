<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeKrs extends Model
{
    use BelongsToTenant;

    protected $table = 'periode_krs';

    protected $fillable = [
        'tenant_id',
        'semester_id',
        'program_studi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_revisi_mulai',
        'tanggal_revisi_selesai',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'tanggal_revisi_mulai' => 'datetime',
        'tanggal_revisi_selesai' => 'datetime',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpenForRegularSubmission(): bool
    {
        return $this->status === 'open' && now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    public function isOpenForRevision(): bool
    {
        return $this->status === 'open'
            && $this->tanggal_revisi_mulai
            && $this->tanggal_revisi_selesai
            && now()->between($this->tanggal_revisi_mulai, $this->tanggal_revisi_selesai);
    }
}
