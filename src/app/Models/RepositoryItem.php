<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepositoryItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'tugas_akhir_id',
        'final_file_id',
        'title',
        'abstract_id',
        'abstract_en',
        'keywords',
        'author_name',
        'nim',
        'program_studi_id',
        'supervisor_names',
        'year',
        'access_level',
        'status',
        'published_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'supervisor_names' => 'array',
        'published_at' => 'datetime',
    ];

    public function tugasAkhir(): BelongsTo
    {
        return $this->belongsTo(TugasAkhir::class);
    }

    public function finalFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'final_file_id');
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }
}
