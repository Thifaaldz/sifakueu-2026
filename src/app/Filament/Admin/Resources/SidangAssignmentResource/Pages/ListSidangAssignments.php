<?php

namespace App\Filament\Admin\Resources\SidangAssignmentResource\Pages;

use App\Filament\Admin\Resources\SidangAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangAssignments extends ListRecords
{
    protected static string $resource = SidangAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
