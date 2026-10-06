<?php

namespace App\Filament\Admin\Resources\KelasKuliahResource\Pages;

use App\Filament\Admin\Resources\KelasKuliahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelasKuliahs extends ListRecords
{
    protected static string $resource = KelasKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
