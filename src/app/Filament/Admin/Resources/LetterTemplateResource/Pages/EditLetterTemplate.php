<?php

namespace App\Filament\Admin\Resources\LetterTemplateResource\Pages;

use App\Filament\Admin\Resources\LetterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterTemplate extends EditRecord
{
    protected static string $resource = LetterTemplateResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
