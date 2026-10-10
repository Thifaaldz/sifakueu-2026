<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratApproval extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'approval_step_id', 'approver_id', 'role_name', 'sequence', 'status', 'notes', 'acted_at', 'approved_at', 'rejected_at'];

    protected $casts = [
        'acted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approvalStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlowStep::class, 'approval_step_id');
    }
}
