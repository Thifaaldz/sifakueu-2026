<?php

namespace App\Filament\Admin\Resources\AlertEscalationResource\Pages;

use App\Filament\Admin\Resources\AlertEscalationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertEscalation extends EditRecord
{
    protected static string $resource = AlertEscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
