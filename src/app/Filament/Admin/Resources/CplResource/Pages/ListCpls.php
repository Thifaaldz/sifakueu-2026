<?php

namespace App\Filament\Admin\Resources\CplResource\Pages;

use App\Filament\Admin\Resources\CplResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCpls extends ListRecords
{
    protected static string $resource = CplResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
