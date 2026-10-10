<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PloResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Plo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PloResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Plo::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kurikulum_id')->relationship('kurikulum', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('code')->required()->maxLength(32),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\Toggle::make('active')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kurikulum.code')->label('Kurikulum')->badge(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->limit(50),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlos::route('/'),
            'create' => Pages\CreatePlo::route('/create'),
            'edit' => Pages\EditPlo::route('/{record}/edit'),
        ];
    }
}
