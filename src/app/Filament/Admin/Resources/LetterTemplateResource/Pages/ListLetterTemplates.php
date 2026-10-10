<?php

namespace App\Filament\Admin\Resources\LetterTemplateResource\Pages;

use App\Filament\Admin\Resources\LetterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterTemplates extends ListRecords
{
    protected static string $resource = LetterTemplateResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
