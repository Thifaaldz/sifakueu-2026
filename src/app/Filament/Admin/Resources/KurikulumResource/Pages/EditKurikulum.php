<?php

namespace App\Filament\Admin\Resources\KurikulumResource\Pages;

use App\Filament\Admin\Resources\KurikulumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKurikulum extends EditRecord
{
    protected static string $resource = KurikulumResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
