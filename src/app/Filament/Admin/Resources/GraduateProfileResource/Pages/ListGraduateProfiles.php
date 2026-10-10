<?php

namespace App\Filament\Admin\Resources\GraduateProfileResource\Pages;

use App\Filament\Admin\Resources\GraduateProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGraduateProfiles extends ListRecords
{
    protected static string $resource = GraduateProfileResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
