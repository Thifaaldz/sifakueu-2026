<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MatriksKesesuaianResource\Pages;
use App\Filament\Admin\Resources\MatriksKesesuaianResource\RelationManagers;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MatriksKesesuaian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MatriksKesesuaianResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MatriksKesesuaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'M5 Analytics';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('mata_kuliah_id')->relationship('mataKuliah', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
                Forms\Components\TextInput::make('rumpun_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('history_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('publication_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('certification_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('preference_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\TextInput::make('overload_penalty')->numeric()->required()->minValue(0),
                Forms\Components\TextInput::make('final_score')->numeric()->required()->minValue(0)->maxValue(100),
                Forms\Components\KeyValue::make('score_breakdown')->columnSpanFull(),
                Forms\Components\Textarea::make('justification')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mataKuliah.name')->label('MK')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dosen.name')->label('Dosen')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rumpun_score')->label('Rumpun')->sortable(),
                Tables\Columns\TextColumn::make('history_score')->label('Riwayat')->sortable(),
                Tables\Columns\TextColumn::make('publication_score')->label('Publikasi')->sortable(),
                Tables\Columns\TextColumn::make('certification_score')->label('Sertifikasi')->sortable(),
                Tables\Columns\TextColumn::make('preference_score')->label('Preferensi')->sortable(),
                Tables\Columns\TextColumn::make('overload_penalty')->label('Penalti')->sortable(),
                Tables\Columns\TextColumn::make('final_score')->label('Final')->badge()->sortable(),
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
            'index' => Pages\ListMatriksKesesuaians::route('/'),
            'create' => Pages\CreateMatriksKesesuaian::route('/create'),
            'edit' => Pages\EditMatriksKesesuaian::route('/{record}/edit'),
        ];
    }
}
