<?php

namespace App\Filament\Admin\Resources\TaCommentResource\Pages;

use App\Filament\Admin\Resources\TaCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaComments extends ListRecords
{
    protected static string $resource = TaCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
