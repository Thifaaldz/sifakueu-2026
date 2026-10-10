<?php

namespace App\Filament\Admin\Resources\ApprovalFlowStepResource\Pages;

use App\Filament\Admin\Resources\ApprovalFlowStepResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovalFlowSteps extends ListRecords
{
    protected static string $resource = ApprovalFlowStepResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
