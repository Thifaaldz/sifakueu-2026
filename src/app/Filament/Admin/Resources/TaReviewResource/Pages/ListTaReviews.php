<?php

namespace App\Filament\Admin\Resources\TaReviewResource\Pages;

use App\Filament\Admin\Resources\TaReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaReviews extends ListRecords
{
    protected static string $resource = TaReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
