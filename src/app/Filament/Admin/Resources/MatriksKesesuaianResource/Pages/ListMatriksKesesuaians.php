<?php

namespace App\Filament\Admin\Resources\MatriksKesesuaianResource\Pages;

use App\Filament\Admin\Resources\MatriksKesesuaianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMatriksKesesuaians extends ListRecords
{
    protected static string $resource = MatriksKesesuaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
