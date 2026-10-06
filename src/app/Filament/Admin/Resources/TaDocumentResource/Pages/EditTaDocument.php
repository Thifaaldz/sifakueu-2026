<?php

namespace App\Filament\Admin\Resources\TaDocumentResource\Pages;

use App\Filament\Admin\Resources\TaDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaDocument extends EditRecord
{
    protected static string $resource = TaDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
