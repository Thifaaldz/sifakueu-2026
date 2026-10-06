<?php

namespace App\Filament\Admin\Resources\DosenPendidikanResource\Pages;

use App\Filament\Admin\Resources\DosenPendidikanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDosenPendidikans extends ListRecords
{
    protected static string $resource = DosenPendidikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
