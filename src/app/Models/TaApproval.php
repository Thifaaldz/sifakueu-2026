<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaApproval extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'ta_document_id', 'approver_id', 'approval_type', 'status', 'note', 'approved_at'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(TaDocument::class, 'ta_document_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'approver_id');
    }
}
