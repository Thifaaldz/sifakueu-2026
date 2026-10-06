<?php

namespace App\Filament\Admin\Resources\SidangRegistrationResource\Pages;

use App\Filament\Admin\Resources\SidangRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangRegistration extends EditRecord
{
    protected static string $resource = SidangRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
