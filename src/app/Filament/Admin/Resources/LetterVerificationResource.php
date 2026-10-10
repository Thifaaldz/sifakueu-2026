<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterVerificationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterVerification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterVerificationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterVerification::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Verifikasi Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('surat_id')->relationship('surat', 'subject')->searchable()->preload()->required(),
            Forms\Components\Select::make('verifier_id')->relationship('verifier', 'name')->label('Verifier')->searchable()->preload(),
            Forms\Components\Select::make('status')->default('PENDING')->options(['PENDING' => 'Pending', 'VERIFIED' => 'Verified', 'REVISION_REQUIRED' => 'Revision', 'REJECTED' => 'Rejected']),
            Forms\Components\DateTimePicker::make('verified_at')->label('Diverifikasi'),
            Forms\Components\Textarea::make('note')->label('Catatan')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surat.request_number')->label('Pengajuan')->searchable(),
            Tables\Columns\TextColumn::make('surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('verifier.name')->label('Verifier')->placeholder('-'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('verified_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterVerifications::route('/'), 'create' => Pages\CreateLetterVerification::route('/create'), 'edit' => Pages\EditLetterVerification::route('/{record}/edit')];
    }
}
