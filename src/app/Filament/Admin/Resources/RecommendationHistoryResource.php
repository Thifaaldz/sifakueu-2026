<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RecommendationHistoryResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RecommendationHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecommendationHistoryResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RecommendationHistory::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'Riwayat Rekomendasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('recommendation_type')->label('Tipe')->required()->maxLength(40),
            Forms\Components\TextInput::make('model_version')->label('Versi Model')->default('M6-RULE-V1')->maxLength(40),
            Forms\Components\DateTimePicker::make('generated_at')->label('Generated'),
            Forms\Components\Textarea::make('payload_json')
                ->label('Payload')
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
            Tables\Columns\TextColumn::make('model_version')->label('Versi'),
            Tables\Columns\TextColumn::make('generated_at')->label('Generated')->dateTime()->sortable(),
        ])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecommendationHistories::route('/'), 'create' => Pages\CreateRecommendationHistory::route('/create'), 'edit' => Pages\EditRecommendationHistory::route('/{record}/edit')];
    }
}
