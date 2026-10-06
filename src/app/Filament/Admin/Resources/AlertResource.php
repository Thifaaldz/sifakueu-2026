<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Alert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Alert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('type')->label('Tipe')->required()->maxLength(100),
            Forms\Components\Select::make('severity')->required()->default('medium')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),
            Forms\Components\Select::make('status')->required()->default('open')->options(['open' => 'Open', 'followed_up' => 'Ditindaklanjuti', 'closed' => 'Closed']),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\Select::make('assigned_to')->relationship('assignee', 'name')->label('Ditugaskan ke')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('escalated_at')->label('Eskalasi pada'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('followUp')
                    ->label('Tindak lanjut')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Alert $record) => $record->status === 'open' && (
                        auth()->user()?->can('followup_student_alert') || auth()->user()?->can('update_alert')
                    ))
                    ->action(fn (Alert $record) => $record->update(['status' => 'followed_up'])),
                Tables\Actions\Action::make('escalate')
                    ->label('Eskalasi')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->visible(fn (Alert $record) => ! $record->escalated_at && auth()->user()?->can('update_alert'))
                    ->action(fn (Alert $record) => $record->update(['escalated_at' => now()])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit' => Pages\EditAlert::route('/{record}/edit'),
        ];
    }
}
