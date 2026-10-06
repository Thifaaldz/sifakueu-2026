<?php

namespace App\Filament\Admin\Resources\TaDocumentResource\Pages;

use App\Filament\Admin\Resources\TaDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaDocuments extends ListRecords
{
    protected static string $resource = TaDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
