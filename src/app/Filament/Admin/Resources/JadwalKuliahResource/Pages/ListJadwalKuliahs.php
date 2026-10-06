<?php

namespace App\Filament\Admin\Resources\JadwalKuliahResource\Pages;

use App\Filament\Admin\Resources\JadwalKuliahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalKuliahs extends ListRecords
{
    protected static string $resource = JadwalKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
