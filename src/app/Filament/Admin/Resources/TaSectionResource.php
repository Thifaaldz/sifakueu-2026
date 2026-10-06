<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaSectionResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaSectionResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaSection::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('sequence')->numeric()->default(0),
            Forms\Components\Toggle::make('required')->default(true),
            Forms\Components\TextInput::make('template_type')->default('content'),
            Forms\Components\Select::make('status')->default('active')->options(['active' => 'Active', 'inactive' => 'Inactive']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('sequence')->sortable(),
            Tables\Columns\TextColumn::make('code')->badge()->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\IconColumn::make('required')->boolean(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaSections::route('/'),
            'create' => Pages\CreateTaSection::route('/create'),
            'edit' => Pages\EditTaSection::route('/{record}/edit'),
        ];
    }
}
