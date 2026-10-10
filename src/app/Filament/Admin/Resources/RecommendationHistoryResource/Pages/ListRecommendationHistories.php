<?php

namespace App\Filament\Admin\Resources\RecommendationHistoryResource\Pages;

use App\Filament\Admin\Resources\RecommendationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecommendationHistories extends ListRecords
{
    protected static string $resource = RecommendationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
