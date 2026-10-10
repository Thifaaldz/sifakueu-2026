<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\LetterFormField;
use App\Models\Surat;
use App\Services\Sifak\LetterWorkflowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
            Forms\Components\Select::make('jenis_surat_id')
                ->relationship('jenisSurat', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set) {
                    $mahasiswa = auth()->user()?->mahasiswa;

                    if (! $mahasiswa) {
                        return;
                    }

                    $set('payload.nama_mahasiswa', $mahasiswa->name);
                    $set('payload.nim', $mahasiswa->nim);
                    $set('payload.program_studi', $mahasiswa->programStudi?->name);
                    $set('payload.semester', $mahasiswa->semester);
                    $set('program_studi_id', $mahasiswa->program_studi_id);
                    $set('requester_type', 'STUDENT');
                }),
            Forms\Components\Select::make('requester_id')
                ->relationship('requester', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id())
                ->disabled(fn () => ! auth()->user()?->can('view_letter_request'))
                ->dehydrated()
                ->required(),
            Forms\Components\Select::make('requester_type')->label('Tipe Pemohon')->default('MULTI')->options([
                'STUDENT' => 'Mahasiswa',
                'LECTURER' => 'Dosen',
                'ADMIN' => 'Admin',
                'MULTI' => 'Multi',
            ]),
            Forms\Components\Select::make('program_studi_id')->relationship('programStudi', 'name')->label('Program Studi')->searchable()->preload(),
            Forms\Components\TextInput::make('subject')->label('Perihal')->required()->maxLength(255),
            Forms\Components\TextInput::make('request_number')->label('Nomor Pengajuan')->disabled()->dehydrated(),
            Forms\Components\TextInput::make('number')->label('Nomor surat')->maxLength(100)->disabled(fn () => ! auth()->user()?->can('override_letter_number'))->dehydrated(fn () => (bool) auth()->user()?->can('override_letter_number')),
            Forms\Components\Select::make('status')->required()->default('DRAFT')->visible(fn () => (bool) auth()->user()?->can('manage_surat'))->options([
                'DRAFT' => 'Draft',
                'SUBMITTED' => 'Diajukan',
                'UNDER_VERIFICATION' => 'Verifikasi',
                'REVISION_REQUIRED' => 'Perlu Revisi',
                'VERIFIED' => 'Terverifikasi',
                'WAITING_APPROVAL' => 'Menunggu Approval',
                'APPROVED' => 'Disetujui',
                'NUMBERED' => 'Bernomor',
                'GENERATING' => 'Generating',
                'GENERATED' => 'Generated',
                'DISTRIBUTED' => 'Terdistrbusi',
                'ARCHIVED' => 'Arsip',
                'REJECTED' => 'Ditolak',
                'CANCELLED' => 'Dibatalkan',
            ]),
            Forms\Components\Section::make('Data Pengajuan')
                ->description('Field mengikuti form dinamis yang diatur pada jenis surat.')
                ->schema(fn (Forms\Get $get): array => static::dynamicPayloadFields($get('jenis_surat_id')))
                ->visible(fn (Forms\Get $get) => filled($get('jenis_surat_id')))
                ->columns(2)
                ->columnSpanFull(),
            Forms\Components\FileUpload::make('attachments')->label('Lampiran')->multiple()->directory('surat/attachments')->columnSpanFull(),
            Forms\Components\Textarea::make('rejection_reason')->label('Catatan Revisi/Penolakan')->disabled(fn () => ! auth()->user()?->can('verify_letter_request'))->dehydrated(fn () => (bool) auth()->user()?->can('verify_letter_request'))->columnSpanFull(),
        ])->columns(2);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function dynamicPayloadFields(mixed $jenisSuratId): array
    {
        if (blank($jenisSuratId)) {
            return [];
        }

        return LetterFormField::query()
            ->where('jenis_surat_id', $jenisSuratId)
            ->where('active', true)
            ->orderBy('sequence')
            ->get()
            ->map(function (LetterFormField $field) {
                $name = 'payload.' . $field->field_key;
                $component = match (strtoupper((string) $field->field_type)) {
                    'TEXTAREA' => Forms\Components\Textarea::make($name)->columnSpanFull(),
                    'NUMBER' => Forms\Components\TextInput::make($name)->numeric(),
                    'DATE' => Forms\Components\DatePicker::make($name),
                    'SELECT' => Forms\Components\Select::make($name)->options(collect($field->options_json ?? [])->mapWithKeys(fn ($option, $key) => [is_int($key) ? $option : $key => $option])->all()),
                    'EMAIL' => Forms\Components\TextInput::make($name)->email(),
                    default => Forms\Components\TextInput::make($name),
                };

                return $component->label($field->label)->required((bool) $field->required);
            })
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('Nomor')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('request_number')->label('Pengajuan')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('jenisSurat.name')->label('Jenis')->searchable(),
                Tables\Columns\TextColumn::make('requester.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Perihal')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'APPROVED', 'NUMBERED', 'GENERATED', 'DISTRIBUTED', 'ARCHIVED' => 'success',
                    'UNDER_VERIFICATION', 'WAITING_APPROVAL', 'GENERATING' => 'warning',
                    'REVISION_REQUIRED' => 'info',
                    'REJECTED', 'CANCELLED' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Surat $record) => in_array($record->status, ['DRAFT', 'REVISION_REQUIRED'], true) && (
                        auth()->user()?->can('submit_letter_request') || auth()->user()?->can('manage_surat')
                    ))
                    ->action(function (Surat $record) {
                        app(LetterWorkflowService::class)->submit($record, auth()->user());
                        Notification::make()->title('Pengajuan surat dikirim')->success()->send();
                    }),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(fn (Surat $record) => in_array($record->status, ['SUBMITTED', 'UNDER_VERIFICATION'], true) && auth()->user()?->can('verify_letter_request'))
                    ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                    ->action(function (Surat $record, array $data) {
                        app(LetterWorkflowService::class)->verify($record, auth()->user(), $data['note'] ?? null);
                        Notification::make()->title('Surat terverifikasi')->success()->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (Surat $record): bool {
                        if (! in_array($record->status, ['WAITING_APPROVAL', 'VERIFIED'], true)) {
                            return false;
                        }

                        $pendingRole = $record->approvals()->where('status', 'PENDING')->orderBy('sequence')->value('role_name');

                        return auth()->user()?->can('approve_letter')
                            || auth()->user()?->can('manage_surat')
                            || ($pendingRole && auth()->user()?->hasRole($pendingRole));
                    })
                    ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                    ->action(function (Surat $record, array $data) {
                        app(LetterWorkflowService::class)->approve($record, auth()->user(), $data['note'] ?? null);
                        Notification::make()->title('Approval diproses')->success()->send();
                    }),
                Tables\Actions\Action::make('revision')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Surat $record) => in_array($record->status, ['UNDER_VERIFICATION', 'WAITING_APPROVAL'], true) && auth()->user()?->can('request_letter_revision'))
                    ->form([Forms\Components\Textarea::make('note')->label('Catatan')->required()])
                    ->action(function (Surat $record, array $data) {
                        app(LetterWorkflowService::class)->requestRevision($record, auth()->user(), $data['note']);
                        Notification::make()->title('Revisi diminta')->warning()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Surat $record) => ! in_array($record->status, ['REJECTED', 'ARCHIVED'], true) && (
                        auth()->user()?->can('reject_letter_request') || auth()->user()?->can('reject_letter')
                    ))
                    ->form([Forms\Components\Textarea::make('reason')->label('Alasan')->required()])
                    ->action(function (Surat $record, array $data) {
                        app(LetterWorkflowService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Surat ditolak')->danger()->send();
                    }),
                Tables\Actions\Action::make('number')
                    ->label('Nomor')
                    ->icon('heroicon-o-hashtag')
                    ->visible(fn (Surat $record) => $record->status === 'APPROVED' && auth()->user()?->can('generate_letter_number'))
                    ->action(function (Surat $record) {
                        app(LetterWorkflowService::class)->generateNumber($record, auth()->user());
                        Notification::make()->title('Nomor surat dibuat')->success()->send();
                    }),
                Tables\Actions\Action::make('generate')
                    ->label('Generate')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn (Surat $record) => in_array($record->status, ['APPROVED', 'NUMBERED'], true) && auth()->user()?->can('generate_letter_document'))
                    ->action(function (Surat $record) {
                        app(LetterWorkflowService::class)->generateDocument($record, auth()->user());
                        Notification::make()->title('Dokumen surat dibuat')->success()->send();
                    }),
                Tables\Actions\Action::make('distribute')
                    ->label('Distribusi')
                    ->icon('heroicon-o-share')
                    ->visible(fn (Surat $record) => $record->status === 'GENERATED' && auth()->user()?->can('distribute_letter'))
                    ->action(fn (Surat $record) => app(LetterWorkflowService::class)->distribute($record, auth()->user())),
                Tables\Actions\Action::make('archive')
                    ->label('Arsip')
                    ->icon('heroicon-o-archive-box')
                    ->visible(fn (Surat $record) => in_array($record->status, ['GENERATED', 'DISTRIBUTED'], true) && auth()->user()?->can('archive_letter'))
                    ->action(fn (Surat $record) => app(LetterWorkflowService::class)->archive($record, auth()->user())),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Surat $record) => filled($record->generated_file_path))
                    ->url(fn (Surat $record) => route('letters.download', $record))
                    ->openUrlInNewTab(),
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
