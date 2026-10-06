<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RepositoriTa extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dokumen_ta_id', 'final_file_path', 'access_status', 'uploaded_at'];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];
}
