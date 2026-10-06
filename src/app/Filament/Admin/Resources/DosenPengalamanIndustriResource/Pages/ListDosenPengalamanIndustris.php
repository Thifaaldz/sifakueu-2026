<?php

namespace App\Filament\Admin\Resources\DosenPengalamanIndustriResource\Pages;

use App\Filament\Admin\Resources\DosenPengalamanIndustriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDosenPengalamanIndustris extends ListRecords
{
    protected static string $resource = DosenPengalamanIndustriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
