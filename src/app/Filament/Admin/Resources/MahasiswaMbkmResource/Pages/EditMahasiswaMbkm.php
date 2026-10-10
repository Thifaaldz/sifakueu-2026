<?php

namespace App\Filament\Admin\Resources\MahasiswaMbkmResource\Pages;

use App\Filament\Admin\Resources\MahasiswaMbkmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaMbkm extends EditRecord
{
    protected static string $resource = MahasiswaMbkmResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
