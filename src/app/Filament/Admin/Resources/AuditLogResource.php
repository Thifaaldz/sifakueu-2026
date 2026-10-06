<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditLogResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Shared Services';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('module')->disabled(),
            Forms\Components\TextInput::make('action')->disabled(),
            Forms\Components\TextInput::make('resource_type')->disabled(),
            Forms\Components\TextInput::make('resource_id')->disabled(),
            Forms\Components\KeyValue::make('old_values')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('new_values')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\Textarea::make('user_agent')->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('role')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('module')->badge()->searchable(),
                Tables\Columns\TextColumn::make('action')->badge()->searchable(),
                Tables\Columns\TextColumn::make('resource_type')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('resource_id')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')->options(fn () => AuditLog::query()->distinct()->pluck('action', 'action')->all()),
                Tables\Filters\SelectFilter::make('module')->options(fn () => AuditLog::query()->whereNotNull('module')->distinct()->pluck('module', 'module')->all()),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
