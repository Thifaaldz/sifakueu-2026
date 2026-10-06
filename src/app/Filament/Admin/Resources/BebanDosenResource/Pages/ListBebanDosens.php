<?php

namespace App\Filament\Admin\Resources\BebanDosenResource\Pages;

use App\Filament\Admin\Resources\BebanDosenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBebanDosens extends ListRecords
{
    protected static string $resource = BebanDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
