<?php

namespace App\Filament\Admin\Resources\RekomendasiPengampuResource\Pages;

use App\Filament\Admin\Resources\RekomendasiPengampuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRekomendasiPengampus extends ListRecords
{
    protected static string $resource = RekomendasiPengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
