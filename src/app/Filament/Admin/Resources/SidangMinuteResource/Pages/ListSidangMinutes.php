<?php

namespace App\Filament\Admin\Resources\SidangMinuteResource\Pages;

use App\Filament\Admin\Resources\SidangMinuteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangMinutes extends ListRecords
{
    protected static string $resource = SidangMinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
