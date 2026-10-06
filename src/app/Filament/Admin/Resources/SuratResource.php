<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Surat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SuratResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'M2 Surat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('jenis_surat_id')->relationship('jenisSurat', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('requester_id')->relationship('requester', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('subject')->label('Perihal')->required()->maxLength(255),
            Forms\Components\TextInput::make('number')->label('Nomor surat')->maxLength(100),
            Forms\Components\Select::make('status')->required()->default('submitted')->options([
                'submitted' => 'Diajukan',
                'in_review' => 'Direview',
                'approved' => 'Disetujui',
                'issued' => 'Terbit',
                'rejected' => 'Ditolak',
            ]),
            Forms\Components\KeyValue::make('payload')->label('Data pengajuan')->columnSpanFull(),
            Forms\Components\FileUpload::make('attachments')->label('Lampiran')->multiple()->directory('surat/attachments')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Nomor')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('jenisSurat.name')->label('Jenis')->searchable(),
                Tables\Columns\TextColumn::make('requester.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Perihal')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('issue')
                    ->label('Terbitkan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Surat $record) => $record->status !== 'issued' && (
                        auth()->user()?->can('generate_nomor_surat') || auth()->user()?->can('manage_surat')
                    ))
                    ->action(fn (Surat $record) => $record->update([
                        'status' => 'issued',
                        'number' => $record->number ?: 'SIFAK/' . now()->format('Ymd') . '/' . str_pad((string) $record->id, 5, '0', STR_PAD_LEFT),
                    ])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurats::route('/'),
            'create' => Pages\CreateSurat::route('/create'),
            'edit' => Pages\EditSurat::route('/{record}/edit'),
        ];
    }
}
