<?php

namespace App\Filament\Admin\Resources\MonitoringOverrideResource\Pages;

use App\Filament\Admin\Resources\MonitoringOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringOverrides extends ListRecords
{
    protected static string $resource = MonitoringOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
