<?php

namespace App\Filament\Admin\Resources\SidangRegistrationResource\Pages;

use App\Filament\Admin\Resources\SidangRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangRegistrations extends ListRecords
{
    protected static string $resource = SidangRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
