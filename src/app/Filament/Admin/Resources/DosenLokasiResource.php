<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenLokasiResource\Pages;
use App\Filament\Admin\Resources\DosenLokasiResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenLokasi;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenLokasiResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenLokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'M5 Availability';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\TextInput::make('latitude')->numeric()->required()->minValue(-90)->maxValue(90),
                Forms\Components\TextInput::make('longitude')->numeric()->required()->minValue(-180)->maxValue(180),
                Forms\Components\TextInput::make('accuracy')->label('Accuracy')->numeric()->minValue(0),
                Forms\Components\Select::make('presence_status')->label('Status')->required()->default('available')->options([
                    'available' => 'Available',
                    'online' => 'Online Consultation',
                    'not_available' => 'Not Available',
                ]),
                Forms\Components\DateTimePicker::make('recorded_at')->label('Captured at')->required(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('presence_status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('latitude')->toggleable(),
                Tables\Columns\TextColumn::make('longitude')->toggleable(),
                Tables\Columns\TextColumn::make('accuracy')->toggleable(),
                Tables\Columns\TextColumn::make('recorded_at')->dateTime()->sortable(),
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
            'index' => Pages\ListDosenLokasis::route('/'),
            'create' => Pages\CreateDosenLokasi::route('/create'),
            'edit' => Pages\EditDosenLokasi::route('/{record}/edit'),
        ];
    }
}
