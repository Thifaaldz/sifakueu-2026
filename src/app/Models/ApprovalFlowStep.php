<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFlowStep extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'approval_flow_id', 'step_order', 'role_code', 'approval_type', 'required', 'can_reject', 'can_request_revision'];

    protected $casts = ['required' => 'boolean', 'can_reject' => 'boolean', 'can_request_revision' => 'boolean'];

    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }
}
