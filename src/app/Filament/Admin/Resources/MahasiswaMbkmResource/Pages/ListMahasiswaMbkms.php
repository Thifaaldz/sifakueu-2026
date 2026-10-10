<?php

namespace App\Filament\Admin\Resources\MahasiswaMbkmResource\Pages;

use App\Filament\Admin\Resources\MahasiswaMbkmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaMbkms extends ListRecords
{
    protected static string $resource = MahasiswaMbkmResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
