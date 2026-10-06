<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WorkflowHistoryResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\WorkflowHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkflowHistoryResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = WorkflowHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Shared Services';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('workflow_type')->disabled(),
            Forms\Components\TextInput::make('resource_type')->disabled(),
            Forms\Components\TextInput::make('resource_id')->disabled(),
            Forms\Components\TextInput::make('from_state')->disabled(),
            Forms\Components\TextInput::make('to_state')->disabled(),
            Forms\Components\TextInput::make('action')->disabled(),
            Forms\Components\Textarea::make('note')->disabled()->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('workflow_type')->badge()->searchable(),
                Tables\Columns\TextColumn::make('resource_type')->limit(35)->toggleable(),
                Tables\Columns\TextColumn::make('resource_id')->searchable(),
                Tables\Columns\TextColumn::make('from_state')->badge(),
                Tables\Columns\TextColumn::make('to_state')->badge(),
                Tables\Columns\TextColumn::make('action')->searchable(),
                Tables\Columns\TextColumn::make('actor.email')->label('Actor')->toggleable(),
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
            'index' => Pages\ListWorkflowHistories::route('/'),
            'view' => Pages\ViewWorkflowHistory::route('/{record}'),
        ];
    }
}
