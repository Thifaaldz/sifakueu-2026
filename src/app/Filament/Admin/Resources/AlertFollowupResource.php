<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertFollowupResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\AlertFollowup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertFollowupResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = AlertFollowup::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('alert_id')->relationship('alert', 'title')->searchable()->preload()->required(),
            Forms\Components\Select::make('actor_id')->relationship('actor', 'name')->label('Aktor')->searchable()->preload(),
            Forms\Components\Select::make('action_type')->required()->default('follow_up')->options([
                'follow_up' => 'Follow-up',
                'counseling' => 'Konseling akademik',
                'reminder' => 'Reminder',
                'coordination' => 'Koordinasi',
            ]),
            Forms\Components\Select::make('status')->required()->default('open')->options(['open' => 'Open', 'done' => 'Selesai', 'cancelled' => 'Batal']),
            Forms\Components\DatePicker::make('next_action_date')->label('Aksi berikutnya'),
            Forms\Components\Textarea::make('note')->label('Catatan')->required()->columnSpanFull(),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alert.mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('alert.mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('alert.title')->label('Alert')->limit(35)->searchable(),
                Tables\Columns\TextColumn::make('actor.name')->label('Aktor'),
                Tables\Columns\TextColumn::make('action_type')->label('Aksi')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('next_action_date')->date(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index' => Pages\ListAlertFollowups::route('/'),
            'create' => Pages\CreateAlertFollowup::route('/create'),
            'edit' => Pages\EditAlertFollowup::route('/{record}/edit'),
        ];
    }
}
