<?php

namespace App\Filament\Admin\Resources\LetterNumberSequenceResource\Pages;

use App\Filament\Admin\Resources\LetterNumberSequenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterNumberSequences extends ListRecords
{
    protected static string $resource = LetterNumberSequenceResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
