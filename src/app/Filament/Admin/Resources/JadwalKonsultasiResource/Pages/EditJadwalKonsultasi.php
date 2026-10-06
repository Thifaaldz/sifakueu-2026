<?php

namespace App\Filament\Admin\Resources\JadwalKonsultasiResource\Pages;

use App\Filament\Admin\Resources\JadwalKonsultasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalKonsultasi extends EditRecord
{
    protected static string $resource = JadwalKonsultasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
