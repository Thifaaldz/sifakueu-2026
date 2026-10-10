<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenPengalamanIndustriResource\Pages;
use App\Filament\Admin\Resources\DosenPengalamanIndustriResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenPengalamanIndustri;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenPengalamanIndustriResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenPengalamanIndustri::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\TextInput::make('institution')->label('Instansi')->required()->maxLength(150),
                Forms\Components\TextInput::make('position')->label('Posisi')->maxLength(150),
                Forms\Components\TextInput::make('field')->label('Bidang')->maxLength(150),
                Forms\Components\DatePicker::make('starts_on')->label('Mulai'),
                Forms\Components\DatePicker::make('ends_on')->label('Selesai')->after('starts_on'),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('institution')->label('Instansi')->searchable(),
                Tables\Columns\TextColumn::make('position')->label('Posisi')->searchable(),
                Tables\Columns\TextColumn::make('field')->label('Bidang')->searchable(),
                Tables\Columns\TextColumn::make('starts_on')->date()->toggleable(),
                Tables\Columns\TextColumn::make('ends_on')->date()->toggleable(),
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
            'index' => Pages\ListDosenPengalamanIndustris::route('/'),
            'create' => Pages\CreateDosenPengalamanIndustri::route('/create'),
            'edit' => Pages\EditDosenPengalamanIndustri::route('/{record}/edit'),
        ];
    }
}
