<?php

namespace App\Filament\Admin\Resources\SifakNotificationResource\Pages;

use App\Filament\Admin\Resources\SifakNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSifakNotifications extends ListRecords
{
    protected static string $resource = SifakNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
