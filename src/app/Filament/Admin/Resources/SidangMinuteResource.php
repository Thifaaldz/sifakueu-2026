<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangMinuteResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangMinute;
use App\Services\Sifak\SidangDocumentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangMinuteResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangMinute::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('document_file_id')->relationship('documentFile', 'original_name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('generated_at'),
            Forms\Components\Select::make('status')->default('draft')->options(['draft' => 'Draft', 'generated' => 'Generated', 'published' => 'Published']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('documentFile.original_name')->label('Dokumen')->limit(40),
            Tables\Columns\TextColumn::make('generated_at')->dateTime(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('generate')
                ->label('Generate')
                ->icon('heroicon-o-document-plus')
                ->visible(fn () => auth()->user()?->can('generate_sidang_minutes') || auth()->user()?->can('update_sidang::minute'))
                ->action(fn (SidangMinute $record) => app(SidangDocumentService::class)->generateMinutes($record->registration)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangMinutes::route('/'),
            'create' => Pages\CreateSidangMinute::route('/create'),
            'edit' => Pages\EditSidangMinute::route('/{record}/edit'),
        ];
    }
}
