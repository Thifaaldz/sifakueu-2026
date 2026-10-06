<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaSection extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'sequence', 'required', 'template_type', 'status'];

    protected $casts = [
        'required' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(TaDocument::class);
    }
}
