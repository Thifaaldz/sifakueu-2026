<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangRequirementResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangRequirementResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangRequirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_type_id')->relationship('type', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('requirement_type')->default('system')->options(['system' => 'System', 'file' => 'File', 'manual' => 'Manual'])->required(),
            Forms\Components\Select::make('source_module')->default('M1')->options(['M1' => 'M1', 'M4' => 'M4', 'M5' => 'M5', 'M7' => 'M7', 'master' => 'Master']),
            Forms\Components\TextInput::make('sequence')->numeric()->default(0),
            Forms\Components\Toggle::make('required')->default(true),
            Forms\Components\Toggle::make('active')->default(true),
            Forms\Components\KeyValue::make('validation_rule')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type.code')->badge(),
            Tables\Columns\TextColumn::make('sequence')->sortable(),
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('requirement_type')->badge(),
            Tables\Columns\IconColumn::make('required')->boolean(),
            Tables\Columns\IconColumn::make('active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangRequirements::route('/'),
            'create' => Pages\CreateSidangRequirement::route('/create'),
            'edit' => Pages\EditSidangRequirement::route('/{record}/edit'),
        ];
    }
}
