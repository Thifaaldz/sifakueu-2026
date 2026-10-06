<?php

namespace App\Filament\Admin\Resources\KbkResource\Pages;

use App\Filament\Admin\Resources\KbkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKbks extends ListRecords
{
    protected static string $resource = KbkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
