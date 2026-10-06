<?php

namespace App\Filament\Admin\Resources\PlottingDosenResource\Pages;

use App\Filament\Admin\Resources\PlottingDosenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlottingDosens extends ListRecords
{
    protected static string $resource = PlottingDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
