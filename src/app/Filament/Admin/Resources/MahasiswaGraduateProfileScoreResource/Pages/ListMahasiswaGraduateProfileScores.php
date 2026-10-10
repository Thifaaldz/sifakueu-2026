<?php

namespace App\Filament\Admin\Resources\MahasiswaGraduateProfileScoreResource\Pages;

use App\Filament\Admin\Resources\MahasiswaGraduateProfileScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaGraduateProfileScores extends ListRecords
{
    protected static string $resource = MahasiswaGraduateProfileScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
