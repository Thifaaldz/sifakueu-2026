<?php

namespace App\Filament\Admin\Resources\SidangScheduleResource\Pages;

use App\Filament\Admin\Resources\SidangScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangSchedule extends EditRecord
{
    protected static string $resource = SidangScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
