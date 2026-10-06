<?php

namespace App\Filament\Admin\Resources\KbkResource\Pages;

use App\Filament\Admin\Resources\KbkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKbk extends EditRecord
{
    protected static string $resource = KbkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
