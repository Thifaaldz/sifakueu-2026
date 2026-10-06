<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JenisSuratResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\JenisSurat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JenisSuratResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = JenisSurat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'M2 Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(32),
            Forms\Components\TextInput::make('name')->label('Jenis surat')->required()->maxLength(255),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            Forms\Components\TagsInput::make('approval_flow')->label('Alur approval')->helperText('Contoh: Admin Prodi, Fakultas, Dekan')->columnSpanFull(),
            Forms\Components\TagsInput::make('merge_fields')->label('Merge field')->columnSpanFull(),
            Forms\Components\RichEditor::make('template_body')->label('Template')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->badge()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Jenis surat')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisSurats::route('/'),
            'create' => Pages\CreateJenisSurat::route('/create'),
            'edit' => Pages\EditJenisSurat::route('/{record}/edit'),
        ];
    }
}
