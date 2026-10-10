<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalFlow extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalFlowStep::class)->orderBy('step_order');
    }
}
