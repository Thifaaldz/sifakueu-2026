<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PemetaanMkCplResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PemetaanMkCpl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PemetaanMkCplResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PemetaanMkCpl::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('cpl_id')->relationship('cpl', 'code')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('weight')->numeric()->required()->default(1)->minValue(0.01),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mataKuliah.code')->label('MK')->badge()->searchable(),
            Tables\Columns\TextColumn::make('mataKuliah.name')->label('Mata Kuliah')->searchable()->limit(45),
            Tables\Columns\TextColumn::make('cpl.code')->label('CPL')->badge()->searchable(),
            Tables\Columns\TextColumn::make('weight')->label('Bobot'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPemetaanMkCpls::route('/'), 'create' => Pages\CreatePemetaanMkCpl::route('/create'), 'edit' => Pages\EditPemetaanMkCpl::route('/{record}/edit')];
    }
}
