<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TemplateDokumen extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'program_studi_id', 'name', 'version', 'citation_style', 'format_rules', 'file_template_path', 'is_active'];

    protected $casts = [
        'format_rules' => 'array',
        'is_active' => 'boolean',
    ];
}
