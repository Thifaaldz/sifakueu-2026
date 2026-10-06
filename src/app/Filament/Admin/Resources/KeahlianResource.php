<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KeahlianResource\Pages;
use App\Filament\Admin\Resources\KeahlianResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Keahlian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KeahlianResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Keahlian::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(40)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')->label('Keahlian')->required()->maxLength(150),
                Forms\Components\Select::make('rumpun_ilmu_id')->relationship('rumpunIlmu', 'name')->searchable()->preload(),
                Forms\Components\Select::make('status')->required()->default('active')->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Keahlian')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rumpunIlmu.name')->label('Rumpun')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
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
            'index' => Pages\ListKeahlians::route('/'),
            'create' => Pages\CreateKeahlian::route('/create'),
            'edit' => Pages\EditKeahlian::route('/{record}/edit'),
        ];
    }
}
