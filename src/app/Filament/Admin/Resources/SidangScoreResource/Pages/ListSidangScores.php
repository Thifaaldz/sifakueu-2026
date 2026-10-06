<?php

namespace App\Filament\Admin\Resources\SidangScoreResource\Pages;

use App\Filament\Admin\Resources\SidangScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSidangScores extends ListRecords
{
    protected static string $resource = SidangScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
