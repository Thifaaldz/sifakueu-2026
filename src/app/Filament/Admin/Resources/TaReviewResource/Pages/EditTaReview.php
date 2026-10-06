<?php

namespace App\Filament\Admin\Resources\TaReviewResource\Pages;

use App\Filament\Admin\Resources\TaReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaReview extends EditRecord
{
    protected static string $resource = TaReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
