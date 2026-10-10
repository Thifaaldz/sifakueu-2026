<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PemetaanCplPloResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\PemetaanCplPlo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PemetaanCplPloResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = PemetaanCplPlo::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('cpl_id')->relationship('cpl', 'code')->searchable()->preload()->required(),
            Forms\Components\Select::make('plo_id')->relationship('plo', 'code')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('weight')->numeric()->required()->default(1)->minValue(0.01),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('cpl.code')->label('CPL')->badge()->searchable(),
            Tables\Columns\TextColumn::make('plo.code')->label('PLO')->badge()->searchable(),
            Tables\Columns\TextColumn::make('plo.name')->label('Profil Lulusan')->searchable()->limit(45),
            Tables\Columns\TextColumn::make('weight')->label('Bobot'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPemetaanCplPlos::route('/'), 'create' => Pages\CreatePemetaanCplPlo::route('/create'), 'edit' => Pages\EditPemetaanCplPlo::route('/{record}/edit')];
    }
}
