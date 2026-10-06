<?php

namespace App\Filament\Admin\Resources\KeahlianResource\Pages;

use App\Filament\Admin\Resources\KeahlianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeahlians extends ListRecords
{
    protected static string $resource = KeahlianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
