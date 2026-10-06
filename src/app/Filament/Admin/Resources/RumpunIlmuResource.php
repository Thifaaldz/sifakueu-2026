<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RumpunIlmuResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\RumpunIlmu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RumpunIlmuResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = RumpunIlmu::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode rumpun')->required()->maxLength(30)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->label('Rumpun ilmu')->required()->maxLength(150),
            Forms\Components\Select::make('status')->required()->default('active')->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif']),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Rumpun ilmu')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(60),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRumpunIlmus::route('/'),
            'create' => Pages\CreateRumpunIlmu::route('/create'),
            'edit' => Pages\EditRumpunIlmu::route('/{record}/edit'),
        ];
    }
}
