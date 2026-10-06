<?php

namespace App\Filament\Concerns;

use App\Support\Access\SifakResourceScope;
use Illuminate\Database\Eloquent\Builder;

trait AppliesSifakResourceScope
{
    public static function getEloquentQuery(): Builder
    {
        return SifakResourceScope::apply(parent::getEloquentQuery(), static::$model);
    }
}
