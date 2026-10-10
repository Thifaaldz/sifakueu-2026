<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterVerificationTokenResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterVerificationToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterVerificationTokenResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterVerificationToken::class;
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Token Verifikasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('generated_letter_id')->relationship('generatedLetter', 'checksum')->label('Dokumen')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('public_token')->label('Token')->required()->maxLength(100),
            Forms\Components\Toggle::make('active')->label('Aktif')->default(true),
            Forms\Components\DateTimePicker::make('expires_at')->label('Kedaluwarsa'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('generatedLetter.surat.number')->label('Nomor')->placeholder('-'),
            Tables\Columns\TextColumn::make('generatedLetter.surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('public_token')->label('Token')->limit(24)->copyable(),
            Tables\Columns\IconColumn::make('active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('expires_at')->dateTime()->placeholder('-'),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterVerificationTokens::route('/'), 'create' => Pages\CreateLetterVerificationToken::route('/create'), 'edit' => Pages\EditLetterVerificationToken::route('/{record}/edit')];
    }
}
