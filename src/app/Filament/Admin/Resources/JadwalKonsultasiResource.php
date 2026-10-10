<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JadwalKonsultasiResource\Pages;
use App\Filament\Admin\Resources\JadwalKonsultasiResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\JadwalKonsultasi;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JadwalKonsultasiResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = JadwalKonsultasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'M5 Availability';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\Select::make('day_of_week')->label('Hari')->required()->options([
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu',
                ]),
                Forms\Components\TimePicker::make('starts_at')->label('Mulai')->required()->seconds(false),
                Forms\Components\TimePicker::make('ends_at')->label('Selesai')->required()->seconds(false)->after('starts_at'),
                Forms\Components\TextInput::make('room')->label('Ruang')->maxLength(255),
                Forms\Components\Select::make('type')->label('Tipe')->required()->default('offline')->options(['offline' => 'Onsite', 'online' => 'Online', 'hybrid' => 'Hybrid']),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('day_of_week')->label('Hari')->formatStateUsing(fn ($state) => [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$state] ?? $state)->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->label('Mulai'),
                Tables\Columns\TextColumn::make('ends_at')->label('Selesai'),
                Tables\Columns\TextColumn::make('room')->label('Ruang')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJadwalKonsultasis::route('/'),
            'create' => Pages\CreateJadwalKonsultasi::route('/create'),
            'edit' => Pages\EditJadwalKonsultasi::route('/{record}/edit'),
        ];
    }
}
