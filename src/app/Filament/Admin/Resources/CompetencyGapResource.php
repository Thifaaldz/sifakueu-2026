<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CompetencyGapResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\CompetencyGap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompetencyGapResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = CompetencyGap::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('competency_type')->options(['CPL' => 'CPL', 'PLO' => 'PLO'])->required(),
            Forms\Components\TextInput::make('competency_reference_id')->numeric()->required(),
            Forms\Components\TextInput::make('current_score')->numeric()->required(),
            Forms\Components\TextInput::make('target_score')->numeric()->default(80),
            Forms\Components\TextInput::make('gap_score')->numeric()->required(),
            Forms\Components\Select::make('severity')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])->required(),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('competency_type')->label('Tipe')->badge(),
            Tables\Columns\TextColumn::make('current_score')->label('Current')->sortable(),
            Tables\Columns\TextColumn::make('target_score')->label('Target'),
            Tables\Columns\TextColumn::make('gap_score')->label('Gap')->sortable(),
            Tables\Columns\TextColumn::make('severity')->badge()->color(fn (?string $state): string => match ($state) {'high' => 'danger', 'medium' => 'warning', default => 'gray'}),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCompetencyGaps::route('/'), 'create' => Pages\CreateCompetencyGap::route('/create'), 'edit' => Pages\EditCompetencyGap::route('/{record}/edit')];
    }
}
