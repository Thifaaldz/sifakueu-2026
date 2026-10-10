<?php

namespace App\Filament\Admin\Resources\MonitoringRuleResource\Pages;

use App\Filament\Admin\Resources\MonitoringRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringRule extends EditRecord
{
    protected static string $resource = MonitoringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
