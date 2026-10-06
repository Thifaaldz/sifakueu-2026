<?php

namespace App\Filament\Admin\Resources\DokumenTaResource\Pages;

use App\Filament\Admin\Resources\DokumenTaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDokumenTas extends ListRecords
{
    protected static string $resource = DokumenTaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
