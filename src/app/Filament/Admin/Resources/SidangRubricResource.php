<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangRubricResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangRubric;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangRubricResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangRubric::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_type_id')->relationship('type', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('weight')->numeric()->default(0)->required(),
            Forms\Components\TextInput::make('min_score')->numeric()->default(0)->required(),
            Forms\Components\TextInput::make('max_score')->numeric()->default(100)->required(),
            Forms\Components\TextInput::make('sequence')->numeric()->default(0),
            Forms\Components\Toggle::make('active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type.code')->badge(),
            Tables\Columns\TextColumn::make('sequence')->sortable(),
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('weight')->suffix('%'),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangRubrics::route('/'),
            'create' => Pages\CreateSidangRubric::route('/create'),
            'edit' => Pages\EditSidangRubric::route('/{record}/edit'),
        ];
    }
}
