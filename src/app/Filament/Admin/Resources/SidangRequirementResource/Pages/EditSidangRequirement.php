<?php

namespace App\Filament\Admin\Resources\SidangRequirementResource\Pages;

use App\Filament\Admin\Resources\SidangRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangRequirement extends EditRecord
{
    protected static string $resource = SidangRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
