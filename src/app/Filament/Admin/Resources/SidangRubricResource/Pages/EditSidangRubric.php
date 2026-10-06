<?php

namespace App\Filament\Admin\Resources\SidangRubricResource\Pages;

use App\Filament\Admin\Resources\SidangRubricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangRubric extends EditRecord
{
    protected static string $resource = SidangRubricResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
