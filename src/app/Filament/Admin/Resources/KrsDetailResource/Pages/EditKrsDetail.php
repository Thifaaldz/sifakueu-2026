<?php

namespace App\Filament\Admin\Resources\KrsDetailResource\Pages;

use App\Filament\Admin\Resources\KrsDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKrsDetail extends EditRecord
{
    protected static string $resource = KrsDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
