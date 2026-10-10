<?php

namespace App\Filament\Admin\Resources\AlertFollowupResource\Pages;

use App\Filament\Admin\Resources\AlertFollowupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertFollowup extends EditRecord
{
    protected static string $resource = AlertFollowupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
