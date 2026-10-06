<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DokumenTa extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'template_dokumen_id',
        'title',
        'table_of_contents',
        'bibliography',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function templateDokumen(): BelongsTo
    {
        return $this->belongsTo(TemplateDokumen::class);
    }

    public function babTas(): HasMany
    {
        return $this->hasMany(BabTa::class);
    }
}
