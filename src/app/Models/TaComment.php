<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaComment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'ta_document_version_id',
        'reviewer_id',
        'comment',
        'page_reference',
        'section_reference',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(TaDocumentVersion::class, 'ta_document_version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'reviewer_id');
    }
}
