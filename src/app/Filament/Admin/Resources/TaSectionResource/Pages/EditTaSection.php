<?php

namespace App\Filament\Admin\Resources\TaSectionResource\Pages;

use App\Filament\Admin\Resources\TaSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaSection extends EditRecord
{
    protected static string $resource = TaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
