<?php

namespace App\Filament\Admin\Resources\KrsResource\Pages;

use App\Filament\Admin\Resources\KrsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKrs extends EditRecord
{
    protected static string $resource = KrsResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
