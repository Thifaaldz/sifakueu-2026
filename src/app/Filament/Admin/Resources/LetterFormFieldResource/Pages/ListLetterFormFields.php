<?php

namespace App\Filament\Admin\Resources\LetterFormFieldResource\Pages;

use App\Filament\Admin\Resources\LetterFormFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterFormFields extends ListRecords
{
    protected static string $resource = LetterFormFieldResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
