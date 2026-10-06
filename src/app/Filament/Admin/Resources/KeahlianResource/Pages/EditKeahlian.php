<?php

namespace App\Filament\Admin\Resources\KeahlianResource\Pages;

use App\Filament\Admin\Resources\KeahlianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeahlian extends EditRecord
{
    protected static string $resource = KeahlianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
