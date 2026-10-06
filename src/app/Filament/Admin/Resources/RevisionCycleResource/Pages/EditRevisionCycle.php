<?php

namespace App\Filament\Admin\Resources\RevisionCycleResource\Pages;

use App\Filament\Admin\Resources\RevisionCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevisionCycle extends EditRecord
{
    protected static string $resource = RevisionCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
