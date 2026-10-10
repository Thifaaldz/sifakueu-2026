<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MonitoringSnapshotResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MonitoringSnapshot;
use App\Services\Sifak\MonitoringEvaluationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringSnapshotResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MonitoringSnapshot::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
            Forms\Components\Select::make('overall_status')->required()->options(['green' => 'Hijau', 'yellow' => 'Kuning', 'red' => 'Merah']),
            Forms\Components\TextInput::make('risk_score')->numeric()->default(0),
            Forms\Components\TextInput::make('total_green')->numeric()->default(0),
            Forms\Components\TextInput::make('total_yellow')->numeric()->default(0),
            Forms\Components\TextInput::make('total_red')->numeric()->default(0),
            Forms\Components\DateTimePicker::make('evaluated_at')->label('Dievaluasi pada'),
            Forms\Components\Textarea::make('summary_payload')
                ->label('Summary Payload')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => json_decode($state ?: '{}', true))
                ->columnSpanFull(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('semester.code')->label('Semester')->toggleable(),
                Tables\Columns\TextColumn::make('overall_status')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'green' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('risk_score')->label('Score')->sortable(),
                Tables\Columns\TextColumn::make('total_green')->label('Hijau'),
                Tables\Columns\TextColumn::make('total_yellow')->label('Kuning'),
                Tables\Columns\TextColumn::make('total_red')->label('Merah'),
                Tables\Columns\TextColumn::make('evaluated_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluasi ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (MonitoringSnapshot $record) => auth()->user()?->can('update_monitoring::snapshot') || auth()->user()?->can('manage_monitoring_rule'))
                    ->action(fn (MonitoringSnapshot $record) => app(MonitoringEvaluationService::class)->evaluate($record->mahasiswa)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringSnapshots::route('/'),
            'create' => Pages\CreateMonitoringSnapshot::route('/create'),
            'edit' => Pages\EditMonitoringSnapshot::route('/{record}/edit'),
        ];
    }
}
