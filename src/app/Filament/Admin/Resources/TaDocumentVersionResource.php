<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaDocumentVersionResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TaDocumentVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaDocumentVersionResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaDocumentVersion::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'M7 Dokumen TA';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('ta_document_id')
                ->relationship(
                    'document',
                    'id',
                    modifyQueryUsing: fn ($query) => self::scopeDocumentOptions($query)
                )
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('version_number')->numeric()->required(),
            Forms\Components\Select::make('stored_file_id')->relationship('storedFile', 'original_name')->searchable()->preload(),
            Forms\Components\Textarea::make('change_summary')->columnSpanFull(),
            Forms\Components\Select::make('status')->default('draft')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'reviewed' => 'Reviewed', 'superseded' => 'Superseded']),
            Forms\Components\TextInput::make('checksum')->maxLength(255),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('document.tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('document.section.code')->label('Bagian')->badge(),
            Tables\Columns\TextColumn::make('version_number')->label('Versi')->badge(),
            Tables\Columns\TextColumn::make('storedFile.original_name')->label('File')->limit(40),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('submitted_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaDocumentVersions::route('/'),
            'create' => Pages\CreateTaDocumentVersion::route('/create'),
            'edit' => Pages\EditTaDocumentVersion::route('/{record}/edit'),
        ];
    }

    private static function scopeDocumentOptions($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('mahasiswa')) {
            return $query->whereHas('tugasAkhir.mahasiswa', fn ($builder) => $builder->where('user_id', $user->id));
        }

        if ($user?->hasAnyRole(['dosen', 'dosen_pembimbing', 'dosen_pa', 'dosen_penguji'])) {
            $dosenId = $user->dosen?->id;

            return $dosenId
                ? $query->whereHas('tugasAkhir', fn ($builder) => $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId))
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
