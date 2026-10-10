<?php

namespace App\Filament\Support;

use Filament\Facades\Filament;
use Filament\Forms;

/**
 * Di panel Mahasiswa, data profil (minat, sertifikasi, portofolio, dst.) hanya boleh diisi untuk diri sendiri.
 */
class MahasiswaOwnership
{
    public static function isSelfService(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'mahasiswa';
    }

    public static function field(string $name = 'mahasiswa_id'): Forms\Components\Select
    {
        return Forms\Components\Select::make($name)
            ->relationship('mahasiswa', 'name', modifyQueryUsing: fn ($query) => static::isSelfService()
                ? $query->where('user_id', auth()->id())
                : $query)
            ->default(fn () => static::isSelfService() ? auth()->user()?->mahasiswa?->id : null)
            ->disabled(fn () => static::isSelfService())
            ->dehydrated()
            ->searchable()
            ->preload()
            ->required();
    }
}
