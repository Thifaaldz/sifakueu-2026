<?php

namespace App\Filament\Admin\Resources\StudentRecommendationResource\Pages;

use App\Filament\Admin\Resources\StudentRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentRecommendations extends ListRecords
{
    protected static string $resource = StudentRecommendationResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
