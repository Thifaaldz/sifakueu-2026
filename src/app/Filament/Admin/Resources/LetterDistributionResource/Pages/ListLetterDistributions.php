<?php

namespace App\Filament\Admin\Resources\LetterDistributionResource\Pages;

use App\Filament\Admin\Resources\LetterDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLetterDistributions extends ListRecords
{
    protected static string $resource = LetterDistributionResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
