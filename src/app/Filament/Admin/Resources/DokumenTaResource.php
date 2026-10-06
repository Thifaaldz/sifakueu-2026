<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DokumenTaResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\DokumenTa;
use App\Services\Sifak\DocumentCompletionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DokumenTaResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = DokumenTa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'M7 Dokumen TA';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dokumen')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
                    Forms\Components\Select::make('template_dokumen_id')->relationship('templateDokumen', 'name')->label('Template')->searchable()->preload(),
                    Forms\Components\TextInput::make('title')->label('Judul TA')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Select::make('status')->required()->default('draft')->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'approved' => 'Disetujui',
                        'final' => 'Final',
                    ]),
                    Forms\Components\DateTimePicker::make('approved_at')->label('Disetujui pada'),
                    Forms\Components\Textarea::make('table_of_contents')->label('Daftar isi')->rows(6)->columnSpanFull(),
                    Forms\Components\Textarea::make('bibliography')->label('Daftar pustaka')->rows(6)->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Bab 1-5')
                ->schema([
                    Forms\Components\Repeater::make('babTas')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('chapter_number')->label('Bab')->numeric()->required()->minValue(1)->maxValue(5),
                            Forms\Components\TextInput::make('title')->label('Judul bab')->required(),
                            Forms\Components\Select::make('status')->required()->default('draft')->options([
                                'draft' => 'Draft',
                                'review' => 'Review',
                                'revision' => 'Revisi',
                                'approved' => 'Disetujui',
                            ]),
                            Forms\Components\RichEditor::make('content')->label('Konten')->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(5)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('approved_at')->label('Disetujui')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Update')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('checkReady')
                    ->label('Cek syarat sidang')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->action(function (DokumenTa $record) {
                        $ready = app(DocumentCompletionService::class)->isReadyForSidang($record);

                        Notification::make()
                            ->title($ready ? 'Dokumen siap untuk sidang' : 'Dokumen belum memenuhi syarat sidang')
                            ->body($ready ? 'Bab 1-5, daftar isi, dan daftar pustaka sudah lengkap.' : 'Pastikan Bab 1-5 approved serta daftar isi dan daftar pustaka terisi.')
                            ->color($ready ? 'success' : 'warning')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDokumenTas::route('/'),
            'create' => Pages\CreateDokumenTa::route('/create'),
            'edit' => Pages\EditDokumenTa::route('/{record}/edit'),
        ];
    }
}
