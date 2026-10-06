<?php

namespace App\Filament\Admin\Resources\PenawaranMataKuliahResource\Pages;

use App\Filament\Admin\Resources\PenawaranMataKuliahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenawaranMataKuliahs extends ListRecords
{
    protected static string $resource = PenawaranMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
