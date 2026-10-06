<?php

namespace App\Filament\Admin\Resources\DosenPreferensiMkResource\Pages;

use App\Filament\Admin\Resources\DosenPreferensiMkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDosenPreferensiMks extends ListRecords
{
    protected static string $resource = DosenPreferensiMkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
