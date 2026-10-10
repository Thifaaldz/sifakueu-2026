<?php

namespace App\Filament\Admin\Resources\MonitoringIndicatorResultResource\Pages;

use App\Filament\Admin\Resources\MonitoringIndicatorResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringIndicatorResult extends EditRecord
{
    protected static string $resource = MonitoringIndicatorResultResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
