<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaDocumentResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Dosen;
use App\Models\TaDocument;
use App\Services\Sifak\TaDocumentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class TaDocumentResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TaDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'M7 Dokumen TA';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tugas_akhir_id')
                ->relationship(
                    'tugasAkhir',
                    'judul',
                    modifyQueryUsing: fn ($query) => self::scopeTugasAkhirOptions($query)
                )
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('ta_section_id')->relationship('section', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('current_version_id')->relationship('currentVersion', 'version_number')->searchable()->preload(),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'submitted' => 'Submitted',
                'in_review' => 'In Review',
                'revision_required' => 'Revision Required',
                'approved' => 'Approved',
                'final' => 'Final',
            ]),
            Forms\Components\Select::make('approved_by')->relationship('approver', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('approved_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('tugasAkhir.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('tugasAkhir.mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('section.name')->label('Bagian')->searchable(),
            Tables\Columns\TextColumn::make('currentVersion.version_number')->label('Versi')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('approved_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\Action::make('uploadVersion')
                ->label('Upload Versi')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (TaDocument $record) => auth()->user()?->can('upload_own_ta_document') || auth()->user()?->can('update', $record))
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File Dokumen')
                        ->storeFiles(false)
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(config('sifak_m7.upload.max_kb', 10240))
                        ->required(),
                    Forms\Components\Textarea::make('summary')->label('Ringkasan Perubahan'),
                ])
                ->action(function (TaDocument $record, array $data) {
                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];

                    if (! $file instanceof UploadedFile) {
                        throw ValidationException::withMessages(['file' => 'File dokumen tidak valid.']);
                    }

                    app(TaDocumentService::class)->uploadVersion($record, $file, $data['summary'] ?? null);
                }),
            Tables\Actions\Action::make('submit')
                ->label('Submit')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (TaDocument $record) => $record->current_version_id && in_array($record->status, ['draft', 'revision_required'], true) && (auth()->user()?->can('submit_ta_document') || auth()->user()?->can('update', $record)))
                ->action(fn (TaDocument $record) => app(TaDocumentService::class)->submit($record)),
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (TaDocument $record) => $record->current_version_id && in_array($record->status, ['submitted', 'under_review'], true) && auth()->user()?->can('approve_ta_document'))
                ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                ->action(function (TaDocument $record, array $data) {
                    $dosen = Dosen::query()->where('user_id', auth()->id())->first();

                    if (! $dosen) {
                        throw ValidationException::withMessages(['dosen' => 'Akun ini belum terhubung ke data dosen.']);
                    }

                    app(TaDocumentService::class)->approve($record->currentVersion, $dosen, $data['note'] ?? null);
                }),
            Tables\Actions\Action::make('revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (TaDocument $record) => $record->current_version_id && in_array($record->status, ['submitted', 'under_review'], true) && auth()->user()?->can('request_ta_revision'))
                ->form([Forms\Components\Textarea::make('summary')->required()])
                ->action(function (TaDocument $record, array $data) {
                    $dosen = Dosen::query()->where('user_id', auth()->id())->first();

                    if (! $dosen) {
                        throw ValidationException::withMessages(['dosen' => 'Akun ini belum terhubung ke data dosen.']);
                    }

                    app(TaDocumentService::class)->requestRevision($record->currentVersion, $dosen, $data['summary']);
                }),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaDocuments::route('/'),
            'create' => Pages\CreateTaDocument::route('/create'),
            'edit' => Pages\EditTaDocument::route('/{record}/edit'),
        ];
    }

    private static function scopeTugasAkhirOptions($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('mahasiswa')) {
            return $query->whereHas('mahasiswa', fn ($builder) => $builder->where('user_id', $user->id));
        }

        if ($user?->hasAnyRole(['dosen', 'dosen_pembimbing', 'dosen_pa', 'dosen_penguji'])) {
            $dosenId = $user->dosen?->id;

            return $dosenId
                ? $query->where(fn ($builder) => $builder->where('pembimbing_1_id', $dosenId)->orWhere('pembimbing_2_id', $dosenId))
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
