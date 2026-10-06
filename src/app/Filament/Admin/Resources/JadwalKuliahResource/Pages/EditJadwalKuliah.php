<?php

namespace App\Filament\Admin\Resources\JadwalKuliahResource\Pages;

use App\Filament\Admin\Resources\JadwalKuliahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJadwalKuliah extends EditRecord
{
    protected static string $resource = JadwalKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
