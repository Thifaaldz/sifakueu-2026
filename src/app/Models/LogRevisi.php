<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LogRevisi extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dokumen_ta_id', 'bab_ta_id', 'dosen_id', 'comment', 'status'];
}
