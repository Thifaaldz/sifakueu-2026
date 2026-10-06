<?php

namespace App\Filament\Admin\Resources\RumpunIlmuResource\Pages;

use App\Filament\Admin\Resources\RumpunIlmuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRumpunIlmus extends ListRecords
{
    protected static string $resource = RumpunIlmuResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
