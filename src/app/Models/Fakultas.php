<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'fakultas';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'short_name',
        'address',
        'email',
        'phone',
        'logo_path',
        'status',
    ];

    public function programStudis(): HasMany
    {
        return $this->hasMany(ProgramStudi::class);
    }
}
