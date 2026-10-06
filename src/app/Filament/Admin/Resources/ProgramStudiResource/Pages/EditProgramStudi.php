<?php

namespace App\Filament\Admin\Resources\ProgramStudiResource\Pages;

use App\Filament\Admin\Resources\ProgramStudiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgramStudi extends EditRecord
{
    protected static string $resource = ProgramStudiResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
