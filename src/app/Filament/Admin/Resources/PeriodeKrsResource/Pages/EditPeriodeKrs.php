<?php

namespace App\Filament\Admin\Resources\PeriodeKrsResource\Pages;

use App\Filament\Admin\Resources\PeriodeKrsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodeKrs extends EditRecord
{
    protected static string $resource = PeriodeKrsResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
