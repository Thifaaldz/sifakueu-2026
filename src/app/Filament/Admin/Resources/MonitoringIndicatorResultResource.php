<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MonitoringIndicatorResultResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MonitoringIndicatorResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringIndicatorResultResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MonitoringIndicatorResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('snapshot_id')->relationship('snapshot', 'id')->required()->searchable()->preload(),
            Forms\Components\Select::make('rule_id')->relationship('rule', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('indicator')->required()->maxLength(100),
            Forms\Components\Select::make('status')->required()->options(['green' => 'Hijau', 'yellow' => 'Kuning', 'red' => 'Merah', 'not_applicable' => 'N/A']),
            Forms\Components\TextInput::make('metric_value')->numeric(),
            Forms\Components\TextInput::make('threshold_value')->numeric(),
            Forms\Components\Textarea::make('explanation')->label('Penjelasan')->columnSpanFull(),
            Forms\Components\TextInput::make('source_reference_type')->maxLength(255),
            Forms\Components\TextInput::make('source_reference_id')->numeric(),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('snapshot.mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('snapshot.mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('rule.domain')->label('Domain')->badge(),
                Tables\Columns\TextColumn::make('rule.name')->label('Rule')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('indicator')->searchable(),
                Tables\Columns\TextColumn::make('metric_value')->label('Nilai'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (?string $state): string => match ($state) {
                    'red' => 'danger',
                    'yellow' => 'warning',
                    'green' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('explanation')->label('Penjelasan')->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringIndicatorResults::route('/'),
            'create' => Pages\CreateMonitoringIndicatorResult::route('/create'),
            'edit' => Pages\EditMonitoringIndicatorResult::route('/{record}/edit'),
        ];
    }
}
