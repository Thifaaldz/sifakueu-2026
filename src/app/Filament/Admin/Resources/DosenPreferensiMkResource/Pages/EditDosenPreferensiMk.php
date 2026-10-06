<?php

namespace App\Filament\Admin\Resources\DosenPreferensiMkResource\Pages;

use App\Filament\Admin\Resources\DosenPreferensiMkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenPreferensiMk extends EditRecord
{
    protected static string $resource = DosenPreferensiMkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
