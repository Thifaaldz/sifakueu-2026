<?php

namespace App\Filament\Admin\Resources\PendaftaranSidangResource\Pages;

use App\Filament\Admin\Resources\PendaftaranSidangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranSidang extends EditRecord
{
    protected static string $resource = PendaftaranSidangResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
