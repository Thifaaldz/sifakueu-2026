<?php

namespace App\Filament\Admin\Resources\SidangScheduleResource\Pages;

use App\Filament\Admin\Resources\SidangScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangSchedules extends ListRecords
{
    protected static string $resource = SidangScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
