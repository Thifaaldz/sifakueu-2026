<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Models\Tenant;
use App\Services\Sifak\TenantProvisioningService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Fakultas / Tenant';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Fakultas')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama fakultas')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->maxLength(32),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->helperText('Huruf kecil, angka, dan tanda hubung. Contoh: fasilkom.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, Forms\Set $set) => $set('slug', app(TenantProvisioningService::class)->normalizeSlug($state ?? ''))),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->default('active')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Tidak aktif',
                            'suspended' => 'Ditangguhkan',
                        ]),
                    Forms\Components\TextInput::make('subdomain')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record?->subdomain),
                    Forms\Components\KeyValue::make('settings')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Admin Tenant Awal')
                ->columns(2)
                ->visible(fn (string $operation) => $operation === 'create')
                ->schema([
                    Forms\Components\TextInput::make('admin_name')
                        ->label('Nama admin')
                        ->required(),
                    Forms\Components\TextInput::make('admin_email')
                        ->label('Email admin')
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('admin_password')
                        ->label('Password awal')
                        ->password()
                        ->revealable()
                        ->default('password')
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Fakultas')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('subdomain')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('database_name')->label('Database')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('provisioned_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak aktif',
                        'suspended' => 'Ditangguhkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('openLocal')
                    ->label('Buka lokal')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Tenant $record) => 'https://' . $record->subdomain . '/admin')
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('createAdmin')
                    ->label('Buat admin')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama admin')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email admin')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password awal')
                            ->password()
                            ->revealable()
                            ->default('password')
                            ->required(),
                    ])
                    ->action(function (Tenant $record, array $data) {
                        app(TenantProvisioningService::class)->provisionDatabase($record);
                        app(TenantProvisioningService::class)->createTenantAdmin($record, $data);

                        Notification::make()
                            ->title('Admin tenant siap dipakai')
                            ->body('Login: ' . $data['email'])
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('provisionDatabase')
                    ->label('Provision DB')
                    ->icon('heroicon-o-circle-stack')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Tenant $record) {
                        app(TenantProvisioningService::class)->provisionDatabase($record);

                        Notification::make()
                            ->title('Database tenant siap')
                            ->body($record->database_name)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Tenant $record) => $record->status !== 'active')
                    ->action(fn (Tenant $record) => $record->update(['status' => 'active'])),
                Tables\Actions\Action::make('suspend')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Tenant $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (Tenant $record) => $record->update(['status' => 'suspended'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
