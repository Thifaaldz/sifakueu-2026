<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TugasAkhirResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\TugasAkhir;
use App\Services\Sifak\TaFinalizationService;
use App\Services\Sifak\TaProgressService;
use App\Services\Sifak\TaRepositoryService;
use App\Services\Sifak\TaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TugasAkhirResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = TugasAkhir::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'M7 Dokumen TA';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')
                ->relationship(
                    'mahasiswa',
                    'name',
                    modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('mahasiswa')
                        ? $query->where('user_id', auth()->id())
                        : $query
                )
                ->default(fn () => auth()->user()?->mahasiswa?->id)
                ->disabled(fn () => auth()->user()?->hasRole('mahasiswa'))
                ->dehydrated()
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('program_studi_id')
                ->relationship(
                    'programStudi',
                    'name',
                    modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('mahasiswa') && auth()->user()?->mahasiswa?->program_studi_id
                        ? $query->whereKey(auth()->user()->mahasiswa->program_studi_id)
                        : $query
                )
                ->default(fn () => auth()->user()?->mahasiswa?->program_studi_id)
                ->disabled(fn () => auth()->user()?->hasRole('mahasiswa'))
                ->dehydrated()
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('pembimbing_1_id')->relationship('pembimbing1', 'name')->label('Pembimbing 1')->searchable()->preload(),
            Forms\Components\Select::make('pembimbing_2_id')->relationship('pembimbing2', 'name')->label('Pembimbing 2')->searchable()->preload(),
            Forms\Components\TextInput::make('judul')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('judul_en')->label('Judul EN')->columnSpanFull(),
            Forms\Components\Textarea::make('topik')->columnSpanFull(),
            Forms\Components\TagsInput::make('keywords')->columnSpanFull(),
            Forms\Components\Select::make('tahun_akademik_id')->relationship('tahunAkademik', 'code')->searchable()->preload(),
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
            Forms\Components\Select::make('status')->required()->default('draft')->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'in_review' => 'In Review',
                'revision' => 'Revision',
                'ready_for_finalization' => 'Ready',
                'finalized' => 'Finalized',
                'archived' => 'Archived',
            ]),
            Forms\Components\TextInput::make('progress_percent')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('judul')->limit(60)->searchable(),
            Tables\Columns\TextColumn::make('pembimbing1.name')->label('Pembimbing')->searchable(),
            Tables\Columns\TextColumn::make('progress_percent')->label('Progress')->suffix('%')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('ensureSections')
                ->label('Buat Struktur')
                ->icon('heroicon-o-square-3-stack-3d')
                ->action(function (TugasAkhir $record) {
                    app(TaService::class)->ensureDefaultSections();
                    app(TaService::class)->ensureDocuments($record);
                }),
            Tables\Actions\Action::make('progress')
                ->label('Hitung Progress')
                ->icon('heroicon-o-chart-bar')
                ->action(fn (TugasAkhir $record) => app(TaProgressService::class)->recalculate($record)),
            Tables\Actions\Action::make('checklist')
                ->label('Checklist')
                ->icon('heroicon-o-clipboard-document-check')
                ->action(function (TugasAkhir $record) {
                    $checklist = app(TaFinalizationService::class)->checklist($record);
                    Notification::make()
                        ->title(in_array(false, $checklist, true) ? 'Belum siap finalisasi' : 'Siap finalisasi')
                        ->body(collect($checklist)->map(fn ($ok, $key) => $key . ': ' . ($ok ? 'OK' : 'Belum'))->implode("\n"))
                        ->color(in_array(false, $checklist, true) ? 'warning' : 'success')
                        ->send();
                }),
            Tables\Actions\Action::make('finalize')
                ->label('Finalisasi')
                ->icon('heroicon-o-lock-closed')
                ->visible(fn () => auth()->user()?->can('finalize_ta_document') || auth()->user()?->can('compile_ta_document'))
                ->action(fn (TugasAkhir $record) => app(TaFinalizationService::class)->finalize($record)),
            Tables\Actions\Action::make('repository')
                ->label('Buat Repository')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->visible(fn (TugasAkhir $record) => $record->status === 'finalized' && auth()->user()?->can('manage_ta_repository'))
                ->action(fn (TugasAkhir $record) => app(TaRepositoryService::class)->createOrUpdate($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTugasAkhirs::route('/'),
            'create' => Pages\CreateTugasAkhir::route('/create'),
            'edit' => Pages\EditTugasAkhir::route('/{record}/edit'),
        ];
    }
}
