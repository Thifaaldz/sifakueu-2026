<?php

namespace App\Filament\Admin\Resources\TaDocumentVersionResource\Pages;

use App\Filament\Admin\Resources\TaDocumentVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaDocumentVersions extends ListRecords
{
    protected static string $resource = TaDocumentVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
