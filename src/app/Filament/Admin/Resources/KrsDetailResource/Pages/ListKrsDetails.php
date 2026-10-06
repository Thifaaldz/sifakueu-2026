<?php

namespace App\Filament\Admin\Resources\KrsDetailResource\Pages;

use App\Filament\Admin\Resources\KrsDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKrsDetails extends ListRecords
{
    protected static string $resource = KrsDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
