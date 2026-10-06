<?php

namespace App\Filament\Admin\Resources\SidangTypeResource\Pages;

use App\Filament\Admin\Resources\SidangTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangTypes extends ListRecords
{
    protected static string $resource = SidangTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
