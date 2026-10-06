<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Plo extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'kurikulum_id', 'code', 'name', 'description'];
}
