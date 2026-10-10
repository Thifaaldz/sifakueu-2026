<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaPloScoreResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaPloScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaPloScoreResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaPloScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('plo_id')->relationship('plo', 'code')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('score')->numeric()->required(),
            Forms\Components\TextInput::make('rank_order')->numeric(),
            Forms\Components\DateTimePicker::make('calculated_at'),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('plo.code')->label('PLO')->badge()->searchable(),
            Tables\Columns\TextColumn::make('plo.name')->label('Nama PLO')->limit(45),
            Tables\Columns\TextColumn::make('score')->sortable(),
            Tables\Columns\TextColumn::make('rank_order')->label('Rank')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaPloScores::route('/'), 'create' => Pages\CreateMahasiswaPloScore::route('/create'), 'edit' => Pages\EditMahasiswaPloScore::route('/{record}/edit')];
    }
}
