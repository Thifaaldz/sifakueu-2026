<?php

namespace App\Filament\Admin\Resources\DosenSertifikasiResource\Pages;

use App\Filament\Admin\Resources\DosenSertifikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenSertifikasi extends EditRecord
{
    protected static string $resource = DosenSertifikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
