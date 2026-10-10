<?php

namespace App\Filament\Admin\Resources\LetterVerificationTokenResource\Pages;

use App\Filament\Admin\Resources\LetterVerificationTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterVerificationTokens extends ListRecords
{
    protected static string $resource = LetterVerificationTokenResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
