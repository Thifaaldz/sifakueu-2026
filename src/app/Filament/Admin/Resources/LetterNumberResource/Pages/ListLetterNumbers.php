<?php

namespace App\Filament\Admin\Resources\LetterNumberResource\Pages;

use App\Filament\Admin\Resources\LetterNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterNumbers extends ListRecords
{
    protected static string $resource = LetterNumberResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
