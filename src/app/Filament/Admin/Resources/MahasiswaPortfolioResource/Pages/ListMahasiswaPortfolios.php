<?php

namespace App\Filament\Admin\Resources\MahasiswaPortfolioResource\Pages;

use App\Filament\Admin\Resources\MahasiswaPortfolioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaPortfolios extends ListRecords
{
    protected static string $resource = MahasiswaPortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
