<?php

namespace App\Filament\Admin\Resources\MahasiswaInterestResource\Pages;

use App\Filament\Admin\Resources\MahasiswaInterestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMahasiswaInterests extends ListRecords
{
    protected static string $resource = MahasiswaInterestResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
