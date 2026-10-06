<?php

namespace App\Filament\Admin\Resources\SidangResultResource\Pages;

use App\Filament\Admin\Resources\SidangResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangResult extends EditRecord
{
    protected static string $resource = SidangResultResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
