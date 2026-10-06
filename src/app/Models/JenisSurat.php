<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSurat extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'approval_flow', 'merge_fields', 'template_body', 'is_active'];

    protected $casts = [
        'approval_flow' => 'array',
        'merge_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class);
    }
}
