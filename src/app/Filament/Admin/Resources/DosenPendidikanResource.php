<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenPendidikanResource\Pages;
use App\Filament\Admin\Resources\DosenPendidikanResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenPendidikan;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenPendidikanResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenPendidikan::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\Select::make('degree')->label('Jenjang')->required()->options(['S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3']),
                Forms\Components\TextInput::make('institution')->label('Institusi')->required()->maxLength(150),
                Forms\Components\TextInput::make('study_program')->label('Program Studi')->maxLength(150),
                Forms\Components\TextInput::make('graduation_year')->label('Tahun Lulus')->numeric()->minValue(1950)->maxValue(2100),
                Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(150),
                Forms\Components\TextInput::make('document_path')->label('Dokumen')->maxLength(255)->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('degree')->label('Jenjang')->badge(),
                Tables\Columns\TextColumn::make('institution')->label('Institusi')->searchable(),
                Tables\Columns\TextColumn::make('field')->label('Bidang')->searchable(),
                Tables\Columns\TextColumn::make('graduation_year')->label('Tahun')->sortable(),
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
            'index' => Pages\ListDosenPendidikans::route('/'),
            'create' => Pages\CreateDosenPendidikan::route('/create'),
            'edit' => Pages\EditDosenPendidikan::route('/{record}/edit'),
        ];
    }
}
