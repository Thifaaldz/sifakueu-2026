<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CplResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Cpl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CplResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Cpl::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kurikulum_id')->relationship('kurikulum', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('code')->required()->maxLength(32),
            Forms\Components\TextInput::make('name')->label('Nama')->maxLength(255),
            Forms\Components\Select::make('category')->label('Kategori')->options([
                'SIKAP' => 'Sikap',
                'PENGETAHUAN' => 'Pengetahuan',
                'KETERAMPILAN_UMUM' => 'Keterampilan Umum',
                'KETERAMPILAN_KHUSUS' => 'Keterampilan Khusus',
            ]),
            Forms\Components\Toggle::make('active')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->required()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kurikulum.code')->label('Kurikulum')->badge(),
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCpls::route('/'),
            'create' => Pages\CreateCpl::route('/create'),
            'edit' => Pages\EditCpl::route('/{record}/edit'),
        ];
    }
}
