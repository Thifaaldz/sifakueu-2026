<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MonitoringOverrideResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MonitoringOverride;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringOverrideResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MonitoringOverride::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('snapshot_id')->relationship('snapshot', 'id')->label('Snapshot')->searchable()->preload(),
            Forms\Components\Select::make('original_status')->required()->options(['green' => 'Hijau', 'yellow' => 'Kuning', 'red' => 'Merah']),
            Forms\Components\Select::make('override_status')->required()->options(['green' => 'Hijau', 'yellow' => 'Kuning', 'red' => 'Merah']),
            Forms\Components\DatePicker::make('valid_until')->label('Berlaku sampai'),
            Forms\Components\Select::make('approved_by')->relationship('approver', 'name')->label('Disetujui oleh')->searchable()->preload(),
            Forms\Components\Textarea::make('reason')->label('Alasan')->required()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('original_status')->label('Asal')->badge(),
                Tables\Columns\TextColumn::make('override_status')->label('Override')->badge()->color(fn (?string $state): string => match ($state) {
                    'red' => 'danger',
                    'yellow' => 'warning',
                    'green' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('valid_until')->date(),
                Tables\Columns\TextColumn::make('approver.name')->label('Approver'),
                Tables\Columns\TextColumn::make('reason')->label('Alasan')->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringOverrides::route('/'),
            'create' => Pages\CreateMonitoringOverride::route('/create'),
            'edit' => Pages\EditMonitoringOverride::route('/{record}/edit'),
        ];
    }
}
