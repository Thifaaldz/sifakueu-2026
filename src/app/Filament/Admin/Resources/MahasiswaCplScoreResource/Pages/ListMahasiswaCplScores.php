<?php

namespace App\Filament\Admin\Resources\MahasiswaCplScoreResource\Pages;

use App\Filament\Admin\Resources\MahasiswaCplScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaCplScores extends ListRecords
{
    protected static string $resource = MahasiswaCplScoreResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
