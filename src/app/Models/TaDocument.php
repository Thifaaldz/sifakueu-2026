<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'tugas_akhir_id',
        'ta_section_id',
        'current_version_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function tugasAkhir(): BelongsTo
    {
        return $this->belongsTo(TugasAkhir::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TaSection::class, 'ta_section_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(TaDocumentVersion::class, 'current_version_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'approved_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(TaDocumentVersion::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TaApproval::class);
    }
}
