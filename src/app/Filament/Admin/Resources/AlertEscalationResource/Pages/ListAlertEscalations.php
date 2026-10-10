<?php

namespace App\Filament\Admin\Resources\AlertEscalationResource\Pages;

use App\Filament\Admin\Resources\AlertEscalationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlertEscalations extends ListRecords
{
    protected static string $resource = AlertEscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
