<?php

namespace App\Filament\Admin\Resources\LetterVerificationResource\Pages;

use App\Filament\Admin\Resources\LetterVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterVerifications extends ListRecords
{
    protected static string $resource = LetterVerificationResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
