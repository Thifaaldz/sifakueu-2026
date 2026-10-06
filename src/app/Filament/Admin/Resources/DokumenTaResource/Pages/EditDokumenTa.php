<?php

namespace App\Filament\Admin\Resources\DokumenTaResource\Pages;

use App\Filament\Admin\Resources\DokumenTaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDokumenTa extends EditRecord
{
    protected static string $resource = DokumenTaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
