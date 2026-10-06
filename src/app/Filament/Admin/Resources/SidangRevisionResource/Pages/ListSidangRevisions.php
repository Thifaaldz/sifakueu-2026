<?php

namespace App\Filament\Admin\Resources\SidangRevisionResource\Pages;

use App\Filament\Admin\Resources\SidangRevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangRevisions extends ListRecords
{
    protected static string $resource = SidangRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
