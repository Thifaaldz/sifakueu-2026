<?php

namespace App\Filament\Admin\Resources\RepositoryItemResource\Pages;

use App\Filament\Admin\Resources\RepositoryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepositoryItems extends ListRecords
{
    protected static string $resource = RepositoryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
