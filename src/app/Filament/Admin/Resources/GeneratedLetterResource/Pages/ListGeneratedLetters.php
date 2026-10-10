<?php

namespace App\Filament\Admin\Resources\GeneratedLetterResource\Pages;

use App\Filament\Admin\Resources\GeneratedLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeneratedLetters extends ListRecords
{
    protected static string $resource = GeneratedLetterResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
