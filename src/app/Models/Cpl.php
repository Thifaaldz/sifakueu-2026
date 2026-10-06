<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'kurikulum_id', 'code', 'description'];
}
