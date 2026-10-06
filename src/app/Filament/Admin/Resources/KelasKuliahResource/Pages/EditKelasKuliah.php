<?php

namespace App\Filament\Admin\Resources\KelasKuliahResource\Pages;

use App\Filament\Admin\Resources\KelasKuliahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelasKuliah extends EditRecord
{
    protected static string $resource = KelasKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
