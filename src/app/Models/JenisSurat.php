<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSurat extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'requester_type',
        'requires_attachment',
        'requires_number',
        'number_pattern',
        'approval_flow_id',
        'approval_flow',
        'merge_fields',
        'template_body',
        'is_active',
    ];

    protected $casts = [
        'requires_attachment' => 'boolean',
        'requires_number' => 'boolean',
        'approval_flow' => 'array',
        'merge_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(LetterFormField::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(LetterTemplate::class);
    }
}
