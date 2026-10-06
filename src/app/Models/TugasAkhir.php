<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TugasAkhir extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'program_studi_id',
        'pembimbing_1_id',
        'pembimbing_2_id',
        'judul',
        'judul_en',
        'topik',
        'keywords',
        'tahun_akademik_id',
        'semester_id',
        'status',
        'progress_percent',
        'final_document_version_id',
    ];

    protected $casts = [
        'keywords' => 'array',
        'progress_percent' => 'decimal:2',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function pembimbing1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_1_id');
    }

    public function pembimbing2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_2_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TaDocument::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(TaProgressLog::class);
    }

    public function revisionCycles(): HasMany
    {
        return $this->hasMany(RevisionCycle::class);
    }

    public function repositoryItem(): HasOne
    {
        return $this->hasOne(RepositoryItem::class);
    }

    public function finalDocumentVersion(): BelongsTo
    {
        return $this->belongsTo(TaDocumentVersion::class, 'final_document_version_id');
    }
}
