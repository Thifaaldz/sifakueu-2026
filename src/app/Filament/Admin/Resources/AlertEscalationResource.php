<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertEscalationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\AlertEscalation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertEscalationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = AlertEscalation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('alert_id')->relationship('alert', 'title')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('from_role')->label('Dari role')->maxLength(80),
            Forms\Components\Select::make('to_role')->label('Ke role')->required()->options([
                'dosen_pa' => 'Dosen PA',
                'admin_prodi' => 'Admin Prodi',
                'kaprodi' => 'Kaprodi',
                'dekan' => 'Dekan',
                'wd' => 'Wakil Dekan',
            ]),
            Forms\Components\Select::make('escalated_by')->relationship('escalator', 'name')->label('Oleh')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('escalated_at')->label('Eskalasi pada'),
            Forms\Components\Textarea::make('reason')->label('Alasan')->required()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alert.mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('alert.mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('alert.title')->label('Alert')->limit(35)->searchable(),
                Tables\Columns\TextColumn::make('from_role')->label('Dari')->badge(),
                Tables\Columns\TextColumn::make('to_role')->label('Ke')->badge(),
                Tables\Columns\TextColumn::make('escalator.name')->label('Oleh'),
                Tables\Columns\TextColumn::make('escalated_at')->dateTime()->sortable(),
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
            'index' => Pages\ListAlertEscalations::route('/'),
            'create' => Pages\CreateAlertEscalation::route('/create'),
            'edit' => Pages\EditAlertEscalation::route('/{record}/edit'),
        ];
    }
}
