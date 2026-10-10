<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangRegistrationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangRegistration;
use App\Services\Sifak\SidangRequirementService;
use App\Services\Sifak\SidangRegistrationService;
use App\Services\Sifak\SidangDocumentService;
use App\Services\Sifak\SidangRecommendationService;
use App\Services\Sifak\SidangScoringService;
use App\Services\Sifak\SidangVerificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangRegistrationResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangRegistration::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_type_id')->relationship('type', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('mahasiswa_id')
                ->relationship('mahasiswa', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('mahasiswa') ? $query->where('user_id', auth()->id()) : $query)
                ->default(fn () => auth()->user()?->mahasiswa?->id)
                ->disabled(fn () => auth()->user()?->hasRole('mahasiswa'))
                ->dehydrated()
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('tugas_akhir_id')->relationship('tugasAkhir', 'judul')->searchable()->preload(),
            Forms\Components\Select::make('semester_id')->relationship('semester', 'code')->searchable()->preload(),
            Forms\Components\Select::make('tahun_akademik_id')->relationship('tahunAkademik', 'code')->searchable()->preload(),
            Forms\Components\TextInput::make('registration_number')->disabled()->dehydrated(false),
            Forms\Components\Select::make('status')->required()->default('draft')->visible(fn () => ! auth()->user()?->hasRole('mahasiswa'))->options([
                'draft' => 'Draft',
                'submitted' => 'Submitted',
                'under_verification' => 'Under Verification',
                'revision_required' => 'Revision Required',
                'verified' => 'Verified',
                'ready_for_plotting' => 'Ready For Plotting',
                'scheduled' => 'Scheduled',
                'revision' => 'Revision',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ]),
            Forms\Components\Textarea::make('rejection_reason')->label('Catatan perbaikan')->disabled(fn () => auth()->user()?->hasRole('mahasiswa'))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration_number')->searchable(),
            Tables\Columns\TextColumn::make('type.code')->badge(),
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('submitted_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('validateRequirements')
                ->label('Validasi Syarat')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['draft', 'submitted', 'under_verification', 'revision_required'], true))
                ->action(fn (SidangRegistration $record) => app(SidangRequirementService::class)->validate($record)),
            Tables\Actions\Action::make('submit')
                ->label('Submit')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['draft', 'revision_required'], true))
                ->action(fn (SidangRegistration $record) => app(SidangRegistrationService::class)->submit($record)),
            Tables\Actions\Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-shield-check')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['submitted', 'under_verification'], true) && auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                ->action(fn (SidangRegistration $record, array $data) => app(SidangVerificationService::class)->verify($record, $data['note'] ?? null)),
            Tables\Actions\Action::make('waiveManual')
                ->label('Waive Manual')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['submitted', 'under_verification'], true) && auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->required()])
                ->action(function (SidangRegistration $record, array $data) {
                    app(SidangRequirementService::class)->validate($record);
                    $record->requirementResults()
                        ->whereHas('requirement', fn ($query) => $query->where('requirement_type', 'manual'))
                        ->update(['status' => 'waived', 'note' => $data['note'], 'checked_by' => auth()->id(), 'checked_at' => now()]);
                }),
            Tables\Actions\Action::make('recommendExaminers')
                ->label('Rekomendasi Penguji')
                ->icon('heroicon-o-sparkles')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['verified', 'ready_for_plotting'], true) && (auth()->user()?->can('assign_examiner') || auth()->user()?->can('manage_sidang_assignment')))
                ->action(function (SidangRegistration $record) {
                    $recommendations = app(SidangRecommendationService::class)->examiners($record)
                        ->map(fn (array $item) => $item['name'] . ' (' . round($item['score'], 2) . ')')
                        ->implode("\n");

                    Notification::make()
                        ->title('Top kandidat penguji')
                        ->body($recommendations ?: 'Belum ada kandidat tersedia.')
                        ->info()
                        ->send();
                }),
            Tables\Actions\Action::make('requestRevision')
                ->label('Minta Perbaikan')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['submitted', 'under_verification'], true) && auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->required()])
                ->action(fn (SidangRegistration $record, array $data) => app(SidangVerificationService::class)->requestRevision($record, $data['note'])),
            Tables\Actions\Action::make('finalizeResult')
                ->label('Finalisasi Hasil')
                ->icon('heroicon-o-trophy')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['scheduled', 'revision'], true) && auth()->user()?->can('finalize_sidang_result'))
                ->form([Forms\Components\Textarea::make('note')->label('Catatan Keputusan')])
                ->action(fn (SidangRegistration $record, array $data) => app(SidangScoringService::class)->finalizeResult($record, $data['note'] ?? null)),
            Tables\Actions\Action::make('generateMinutes')
                ->label('Berita Acara')
                ->icon('heroicon-o-document-plus')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['scheduled', 'revision', 'completed'], true) && auth()->user()?->can('generate_sidang_minutes'))
                ->action(fn (SidangRegistration $record) => app(SidangDocumentService::class)->generateMinutes($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangRegistrations::route('/'),
            'create' => Pages\CreateSidangRegistration::route('/create'),
            'edit' => Pages\EditSidangRegistration::route('/{record}/edit'),
        ];
    }
}
