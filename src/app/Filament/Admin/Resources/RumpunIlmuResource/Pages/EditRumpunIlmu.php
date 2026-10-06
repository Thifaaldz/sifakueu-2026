<?php

namespace App\Filament\Admin\Resources\RumpunIlmuResource\Pages;

use App\Filament\Admin\Resources\RumpunIlmuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRumpunIlmu extends EditRecord
{
    protected static string $resource = RumpunIlmuResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
