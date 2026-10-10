<?php

namespace App\Filament\Admin\Resources\MonitoringOverrideResource\Pages;

use App\Filament\Admin\Resources\MonitoringOverrideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringOverride extends EditRecord
{
    protected static string $resource = MonitoringOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
