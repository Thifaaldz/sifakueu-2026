<?php

namespace App\Filament\Admin\Resources\LetterAttachmentResource\Pages;

use App\Filament\Admin\Resources\LetterAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterAttachments extends ListRecords
{
    protected static string $resource = LetterAttachmentResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
