<?php

namespace App\Filament\Admin\Resources\SidangMinuteResource\Pages;

use App\Filament\Admin\Resources\SidangMinuteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangMinute extends EditRecord
{
    protected static string $resource = SidangMinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
