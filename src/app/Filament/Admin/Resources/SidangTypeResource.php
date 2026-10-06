<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangTypeResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangTypeResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangType::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->maxLength(40),
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->badge()->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangTypes::route('/'),
            'create' => Pages\CreateSidangType::route('/create'),
            'edit' => Pages\EditSidangType::route('/{record}/edit'),
        ];
    }
}
