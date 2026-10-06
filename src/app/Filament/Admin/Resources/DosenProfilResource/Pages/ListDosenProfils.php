<?php

namespace App\Filament\Admin\Resources\DosenProfilResource\Pages;

use App\Filament\Admin\Resources\DosenProfilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDosenProfils extends ListRecords
{
    protected static string $resource = DosenProfilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
