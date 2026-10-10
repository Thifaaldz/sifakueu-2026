<?php

namespace App\Filament\Admin\Resources\LetterArchiveResource\Pages;

use App\Filament\Admin\Resources\LetterArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterArchives extends ListRecords
{
    protected static string $resource = LetterArchiveResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
