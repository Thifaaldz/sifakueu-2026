<?php

namespace App\Filament\Admin\Resources\TaDocumentVersionResource\Pages;

use App\Filament\Admin\Resources\TaDocumentVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaDocumentVersion extends EditRecord
{
    protected static string $resource = TaDocumentVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
