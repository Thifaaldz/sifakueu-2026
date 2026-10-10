<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaInterestResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaInterest;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaInterestResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaInterest::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('interest_area')->label('Minat')->required(),
            Forms\Components\Select::make('level')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])->default('medium')->required(),
            Forms\Components\TextInput::make('source')->default('self_reported')->visible(fn () => ! MahasiswaOwnership::isSelfService()),
            Forms\Components\Toggle::make('verified')->default(false)->visible(fn () => ! MahasiswaOwnership::isSelfService()),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('interest_area')->label('Minat')->searchable()->badge(),
            Tables\Columns\TextColumn::make('level')->badge(),
            Tables\Columns\IconColumn::make('verified')->boolean(),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaInterests::route('/'), 'create' => Pages\CreateMahasiswaInterest::route('/create'), 'edit' => Pages\EditMahasiswaInterest::route('/{record}/edit')];
    }
}
