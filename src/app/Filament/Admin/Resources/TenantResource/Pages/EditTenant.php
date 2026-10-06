<?php

namespace App\Filament\Admin\Resources\TenantResource\Pages;

use App\Filament\Admin\Resources\TenantResource;
use App\Services\Sifak\TenantProvisioningService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        app(TenantProvisioningService::class)->validateSlug($data['slug'], $this->record->id);
        $data['subdomain'] = app(TenantProvisioningService::class)->subdomain($data['slug']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
