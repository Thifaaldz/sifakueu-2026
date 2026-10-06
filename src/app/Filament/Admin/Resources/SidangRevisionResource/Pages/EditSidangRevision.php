<?php

namespace App\Filament\Admin\Resources\SidangRevisionResource\Pages;

use App\Filament\Admin\Resources\SidangRevisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSidangRevision extends EditRecord
{
    protected static string $resource = SidangRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
