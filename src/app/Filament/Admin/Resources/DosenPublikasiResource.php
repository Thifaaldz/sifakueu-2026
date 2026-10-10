<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenPublikasiResource\Pages;
use App\Filament\Admin\Resources\DosenPublikasiResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenPublikasi;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenPublikasiResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenPublikasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('year')->label('Tahun')->numeric()->minValue(1950)->maxValue(2100),
                Forms\Components\TextInput::make('type')->label('Jenis')->maxLength(60),
                Forms\Components\TextInput::make('publisher')->label('Jurnal/Penerbit')->maxLength(180),
                Forms\Components\TextInput::make('doi_url')->label('DOI/URL')->url()->maxLength(255),
                Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(150),
                Forms\Components\TagsInput::make('keywords')->label('Kata Kunci')->columnSpanFull(),
                Forms\Components\Select::make('source')->label('Sumber')->default('manual')->options([
                    'manual' => 'Manual',
                    'sinta' => 'SINTA',
                    'google_scholar' => 'Google Scholar',
                    'internal_repository' => 'Repository Internal',
                ]),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('year')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('field')->label('Bidang')->searchable(),
                Tables\Columns\TextColumn::make('source')->badge(),
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
            'index' => Pages\ListDosenPublikasis::route('/'),
            'create' => Pages\CreateDosenPublikasi::route('/create'),
            'edit' => Pages\EditDosenPublikasi::route('/{record}/edit'),
        ];
    }
}
