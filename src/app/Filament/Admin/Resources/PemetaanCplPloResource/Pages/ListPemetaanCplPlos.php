<?php

namespace App\Filament\Admin\Resources\PemetaanCplPloResource\Pages;

use App\Filament\Admin\Resources\PemetaanCplPloResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPemetaanCplPlos extends ListRecords
{
    protected static string $resource = PemetaanCplPloResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
