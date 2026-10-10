<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DosenPreferensiMkResource\Pages;
use App\Filament\Admin\Resources\DosenPreferensiMkResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DosenPreferensiMk;
use App\Filament\Support\DosenOwnership;
use Filament\Forms;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DosenPreferensiMkResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DosenPreferensiMk::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'M5 Profiling Dosen';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DosenOwnership::field(),
                Forms\Components\Select::make('mata_kuliah_id')
                ->relationship('mataKuliah', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule
                    ->where('dosen_id', $get('dosen_id'))
                    ->where('semester_id', $get('semester_id')))
                ->validationMessages(['unique' => 'Preferensi untuk mata kuliah dan semester ini sudah ada.']),
                Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
                Forms\Components\Select::make('preference_level')->label('Tingkat Preferensi')->required()->default(3)->options([
                    1 => 'Sangat rendah',
                    2 => 'Rendah',
                    3 => 'Sedang',
                    4 => 'Tinggi',
                    5 => 'Sangat tinggi',
                ]),
                Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mataKuliah.name')->label('MK')->searchable(),
                Tables\Columns\TextColumn::make('semester.code')->label('Semester')->toggleable(),
                Tables\Columns\TextColumn::make('preference_level')->label('Preferensi')->badge()->sortable(),
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
            'index' => Pages\ListDosenPreferensiMks::route('/'),
            'create' => Pages\CreateDosenPreferensiMk::route('/create'),
            'edit' => Pages\EditDosenPreferensiMk::route('/{record}/edit'),
        ];
    }
}
