<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GraduateProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'program_studi_id', 'code', 'name', 'description', 'active', 'metadata'];

    protected $casts = ['active' => 'boolean', 'metadata' => 'array'];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function plos(): BelongsToMany
    {
        return $this->belongsToMany(Plo::class, 'graduate_profile_plo')
            ->withPivot(['tenant_id', 'weight'])
            ->withTimestamps();
    }
}
