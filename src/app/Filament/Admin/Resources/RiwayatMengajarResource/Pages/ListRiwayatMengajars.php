<?php

namespace App\Filament\Admin\Resources\RiwayatMengajarResource\Pages;

use App\Filament\Admin\Resources\RiwayatMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatMengajars extends ListRecords
{
    protected static string $resource = RiwayatMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
