<?php

namespace App\Filament\Admin\Resources\RiwayatMengajarResource\Pages;

use App\Filament\Admin\Resources\RiwayatMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatMengajar extends EditRecord
{
    protected static string $resource = RiwayatMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
