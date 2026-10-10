<?php

namespace App\Filament\Admin\Resources\LetterFormFieldResource\Pages;

use App\Filament\Admin\Resources\LetterFormFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLetterFormField extends EditRecord
{
    protected static string $resource = LetterFormFieldResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
