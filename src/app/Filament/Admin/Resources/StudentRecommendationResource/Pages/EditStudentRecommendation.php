<?php

namespace App\Filament\Admin\Resources\StudentRecommendationResource\Pages;

use App\Filament\Admin\Resources\StudentRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentRecommendation extends EditRecord
{
    protected static string $resource = StudentRecommendationResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
