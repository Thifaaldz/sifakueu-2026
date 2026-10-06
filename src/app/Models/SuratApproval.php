<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratApproval extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'approver_id', 'role_name', 'sequence', 'status', 'notes', 'acted_at'];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
