<?php

namespace App\Filament\Admin\Resources\DosenPublikasiResource\Pages;

use App\Filament\Admin\Resources\DosenPublikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosenPublikasi extends EditRecord
{
    protected static string $resource = DosenPublikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
