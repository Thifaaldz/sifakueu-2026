<?php

namespace App\Filament\Concerns;

use App\Support\Access\ResourceLabels;
use App\Support\Access\SifakResourceScope;
use Illuminate\Database\Eloquent\Builder;

trait AppliesSifakResourceScope
{
    public static function getEloquentQuery(): Builder
    {
        return SifakResourceScope::apply(parent::getEloquentQuery(), static::$model);
    }

    public static function getModelLabel(): string
    {
        return ResourceLabels::for(static::class) ?? parent::getModelLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return ResourceLabels::for(static::class) ?? parent::getPluralModelLabel();
    }
}
