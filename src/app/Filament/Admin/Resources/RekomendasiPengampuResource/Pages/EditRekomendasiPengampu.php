<?php

namespace App\Filament\Admin\Resources\RekomendasiPengampuResource\Pages;

use App\Filament\Admin\Resources\RekomendasiPengampuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekomendasiPengampu extends EditRecord
{
    protected static string $resource = RekomendasiPengampuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
