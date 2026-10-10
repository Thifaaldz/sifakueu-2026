<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LetterDistributionResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterDistribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LetterDistributionResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = LetterDistribution::class;
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'M2 Surat';
    protected static ?string $navigationLabel = 'Distribusi Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('generated_letter_id')->relationship('generatedLetter', 'checksum')->label('Dokumen')->searchable()->preload()->required(),
            Forms\Components\Select::make('channel')->default('DOWNLOAD')->options(['DOWNLOAD' => 'Download', 'EMAIL' => 'Email', 'IN_APP' => 'In-App', 'WHATSAPP' => 'WhatsApp']),
            Forms\Components\TextInput::make('recipient')->label('Penerima'),
            Forms\Components\Select::make('status')->default('PENDING')->options(['PENDING' => 'Pending', 'SENT' => 'Sent', 'FAILED' => 'Failed']),
            Forms\Components\DateTimePicker::make('sent_at')->label('Terkirim'),
            Forms\Components\Textarea::make('error_message')->label('Error')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('generatedLetter.surat.number')->label('Nomor')->placeholder('-'),
            Tables\Columns\TextColumn::make('generatedLetter.surat.subject')->label('Perihal')->limit(35),
            Tables\Columns\TextColumn::make('channel')->badge(),
            Tables\Columns\TextColumn::make('recipient')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLetterDistributions::route('/'), 'create' => Pages\CreateLetterDistribution::route('/create'), 'edit' => Pages\EditLetterDistribution::route('/{record}/edit')];
    }
}
