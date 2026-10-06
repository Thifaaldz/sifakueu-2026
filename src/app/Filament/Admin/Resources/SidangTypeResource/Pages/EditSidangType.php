<?php

namespace App\Filament\Admin\Resources\SidangTypeResource\Pages;

use App\Filament\Admin\Resources\SidangTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangType extends EditRecord
{
    protected static string $resource = SidangTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
