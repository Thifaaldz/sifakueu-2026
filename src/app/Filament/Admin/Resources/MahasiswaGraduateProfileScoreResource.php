<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaGraduateProfileScoreResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaGraduateProfileScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaGraduateProfileScoreResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaGraduateProfileScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'Skor Profil Lulusan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('graduate_profile_id')->relationship('graduateProfile', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('score')->numeric()->default(0),
            Forms\Components\TextInput::make('rank_order')->label('Rank')->numeric(),
            Forms\Components\DateTimePicker::make('generated_at')->label('Generated'),
            Forms\Components\Textarea::make('payload')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? json_decode($state, true) : null)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('graduateProfile.name')->label('Profil Lulusan')->searchable(),
            Tables\Columns\TextColumn::make('score')->sortable(),
            Tables\Columns\TextColumn::make('rank_order')->label('Rank')->sortable(),
            Tables\Columns\TextColumn::make('generated_at')->label('Generated')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaGraduateProfileScores::route('/'), 'create' => Pages\CreateMahasiswaGraduateProfileScore::route('/create'), 'edit' => Pages\EditMahasiswaGraduateProfileScore::route('/{record}/edit')];
    }
}
