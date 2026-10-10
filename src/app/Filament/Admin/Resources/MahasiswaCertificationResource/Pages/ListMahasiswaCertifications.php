<?php

namespace App\Filament\Admin\Resources\MahasiswaCertificationResource\Pages;

use App\Filament\Admin\Resources\MahasiswaCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaCertifications extends ListRecords
{
    protected static string $resource = MahasiswaCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
