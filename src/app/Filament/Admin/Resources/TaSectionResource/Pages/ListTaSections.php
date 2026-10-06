<?php

namespace App\Filament\Admin\Resources\TaSectionResource\Pages;

use App\Filament\Admin\Resources\TaSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaSections extends ListRecords
{
    protected static string $resource = TaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
