<?php

namespace App\Filament\Admin\Resources\PemetaanMkCplResource\Pages;

use App\Filament\Admin\Resources\PemetaanMkCplResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPemetaanMkCpls extends ListRecords
{
    protected static string $resource = PemetaanMkCplResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
