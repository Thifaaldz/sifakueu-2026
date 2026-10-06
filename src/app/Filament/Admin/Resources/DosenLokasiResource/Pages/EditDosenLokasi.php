<?php

namespace App\Filament\Admin\Resources\DosenLokasiResource\Pages;

use App\Filament\Admin\Resources\DosenLokasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenLokasi extends EditRecord
{
    protected static string $resource = DosenLokasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
