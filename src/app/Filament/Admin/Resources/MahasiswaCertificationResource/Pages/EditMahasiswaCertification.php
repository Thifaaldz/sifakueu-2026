<?php

namespace App\Filament\Admin\Resources\MahasiswaCertificationResource\Pages;

use App\Filament\Admin\Resources\MahasiswaCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaCertification extends EditRecord
{
    protected static string $resource = MahasiswaCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
