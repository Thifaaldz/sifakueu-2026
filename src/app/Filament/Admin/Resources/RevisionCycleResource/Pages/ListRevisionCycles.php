<?php

namespace App\Filament\Admin\Resources\RevisionCycleResource\Pages;

use App\Filament\Admin\Resources\RevisionCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevisionCycles extends ListRecords
{
    protected static string $resource = RevisionCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
