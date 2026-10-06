<?php

namespace App\Filament\Admin\Resources\TugasAkhirResource\Pages;

use App\Filament\Admin\Resources\TugasAkhirResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTugasAkhir extends EditRecord
{
    protected static string $resource = TugasAkhirResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
