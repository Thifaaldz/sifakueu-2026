<?php

namespace App\Filament\Admin\Resources\DosenPendidikanResource\Pages;

use App\Filament\Admin\Resources\DosenPendidikanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenPendidikan extends EditRecord
{
    protected static string $resource = DosenPendidikanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
