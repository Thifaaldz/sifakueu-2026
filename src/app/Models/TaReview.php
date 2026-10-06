<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaReview extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'ta_document_version_id', 'reviewer_id', 'review_status', 'summary', 'reviewed_at'];

    protected $casts = [
        'reviewed_at' => 'datetime',
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
