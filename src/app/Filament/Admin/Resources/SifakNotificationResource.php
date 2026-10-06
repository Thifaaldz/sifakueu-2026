<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SifakNotificationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SifakNotification;
use App\Services\Shared\Notification\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SifakNotificationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SifakNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Shared Services';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'email')->searchable()->preload()->required(),
            Forms\Components\Select::make('channel')->required()->options(['in_app' => 'In-App', 'email' => 'Email', 'whatsapp' => 'WhatsApp']),
            Forms\Components\TextInput::make('type')->required()->maxLength(80),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('message')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('reference_type')->maxLength(255),
            Forms\Components\TextInput::make('reference_id')->maxLength(255),
            Forms\Components\Select::make('status')->required()->options([
                'pending' => 'Pending',
                'queued' => 'Queued',
                'sent' => 'Sent',
                'failed' => 'Failed',
                'read' => 'Read',
            ]),
            Forms\Components\Textarea::make('failed_reason')->columnSpanFull(),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('channel')->badge(),
                Tables\Columns\TextColumn::make('type')->badge()->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('read_at')->dateTime()->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (SifakNotification $record) => $record->status === 'failed')
                    ->action(fn (SifakNotification $record) => app(NotificationService::class)->queue($record)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSifakNotifications::route('/'),
            'create' => Pages\CreateSifakNotification::route('/create'),
            'edit' => Pages\EditSifakNotification::route('/{record}/edit'),
        ];
    }
}
