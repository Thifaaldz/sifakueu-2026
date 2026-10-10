<?php

namespace App\Filament\Admin\Resources\MahasiswaOrganizationResource\Pages;

use App\Filament\Admin\Resources\MahasiswaOrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMahasiswaOrganization extends EditRecord
{
    protected static string $resource = MahasiswaOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
