<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ScheduledTaskLogResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\ScheduledTaskLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduledTaskLogResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = ScheduledTaskLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Shared Services';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('task_name')->disabled(),
            Forms\Components\DateTimePicker::make('started_at')->disabled(),
            Forms\Components\DateTimePicker::make('finished_at')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('message')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('context')->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('task_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('started_at')->dateTime(),
                Tables\Columns\TextColumn::make('finished_at')->dateTime(),
                Tables\Columns\TextColumn::make('message')->limit(60),
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
            'index' => Pages\ListScheduledTaskLogs::route('/'),
            'view' => Pages\ViewScheduledTaskLog::route('/{record}'),
        ];
    }
}
