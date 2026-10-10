<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaCplScoreResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaCplScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaCplScoreResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaCplScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('cpl_id')->relationship('cpl', 'code')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('score')->numeric()->required(),
            Forms\Components\TextInput::make('semester')->maxLength(16),
            Forms\Components\TextInput::make('source')->default('M6'),
            Forms\Components\DateTimePicker::make('calculated_at'),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('cpl.code')->label('CPL')->badge()->searchable(),
            Tables\Columns\TextColumn::make('cpl.name')->label('Nama CPL')->limit(45),
            Tables\Columns\TextColumn::make('score')->sortable(),
            Tables\Columns\TextColumn::make('calculated_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaCplScores::route('/'), 'create' => Pages\CreateMahasiswaCplScore::route('/create'), 'edit' => Pages\EditMahasiswaCplScore::route('/{record}/edit')];
    }
}
