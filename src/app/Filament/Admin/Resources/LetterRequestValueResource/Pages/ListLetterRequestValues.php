<?php

namespace App\Filament\Admin\Resources\LetterRequestValueResource\Pages;

use App\Filament\Admin\Resources\LetterRequestValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterRequestValues extends ListRecords
{
    protected static string $resource = LetterRequestValueResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
