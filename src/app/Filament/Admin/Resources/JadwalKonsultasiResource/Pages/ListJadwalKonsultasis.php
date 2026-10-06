<?php

namespace App\Filament\Admin\Resources\JadwalKonsultasiResource\Pages;

use App\Filament\Admin\Resources\JadwalKonsultasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJadwalKonsultasis extends ListRecords
{
    protected static string $resource = JadwalKonsultasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
