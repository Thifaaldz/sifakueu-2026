<?php

namespace App\Filament\Support;

use Filament\Facades\Filament;
use Filament\Forms;

/**
 * Di panel Dosen, data profil (pendidikan, sertifikasi, publikasi, dst.) hanya boleh diisi untuk diri sendiri.
 */
class DosenOwnership
{
    public static function isSelfService(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'dosen';
    }

    public static function field(string $name = 'dosen_id'): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->relationship('dosen', 'name', modifyQueryUsing: fn ($query) => static::isSelfService()
                ? $query->where('user_id', auth()->id())
                : $query)
            ->default(fn () => static::isSelfService() ? auth()->user()?->dosen?->id : null)
            ->disabled(fn () => static::isSelfService())
            ->dehydrated()
            ->searchable()
            ->preload()
            ->required();
    }
}
