<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StudentRecommendationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\StudentRecommendation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentRecommendationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = StudentRecommendation::class;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('recommendation_type')->required()->options(['COURSE' => 'Mata Kuliah', 'GRADUATE_PROFILE' => 'Profil Lulusan', 'CAREER' => 'Karier', 'THESIS_TOPIC' => 'Topik TA', 'SUPERVISOR' => 'Pembimbing']),
            Forms\Components\TextInput::make('score')->numeric()->default(0),
            Forms\Components\TextInput::make('rank_order')->numeric(),
            Forms\Components\Select::make('status')->default('generated')->options(['generated' => 'Generated', 'viewed' => 'Viewed', 'accepted_as_reference' => 'Accepted as Reference', 'dismissed' => 'Dismissed', 'superseded' => 'Superseded']),
            Forms\Components\Textarea::make('reason_summary')->label('Alasan')->columnSpanFull(),
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
            Tables\Columns\TextColumn::make('recommendation_type')->label('Tipe')->badge()->sortable(),
            Tables\Columns\TextColumn::make('score')->sortable(),
            Tables\Columns\TextColumn::make('rank_order')->label('Rank')->sortable(),
            Tables\Columns\TextColumn::make('reason_summary')->label('Alasan')->limit(55),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStudentRecommendations::route('/'), 'create' => Pages\CreateStudentRecommendation::route('/create'), 'edit' => Pages\EditStudentRecommendation::route('/{record}/edit')];
    }
}
