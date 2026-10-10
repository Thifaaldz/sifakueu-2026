<?php

namespace App\Filament\Admin\Resources\MonitoringSnapshotResource\Pages;

use App\Filament\Admin\Resources\MonitoringSnapshotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringSnapshots extends ListRecords
{
    protected static string $resource = MonitoringSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
