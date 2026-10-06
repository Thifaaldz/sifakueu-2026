<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangRegistrationResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangRegistration;
use App\Services\Sifak\SidangRequirementService;
use App\Services\Sifak\SidangRegistrationService;
use App\Services\Sifak\SidangVerificationService;
use Filament\Forms;
use Filament\Forms\Form;
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
            Forms\Components\Select::make('status')->required()->default('draft')->options([
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
            Forms\Components\Textarea::make('rejection_reason')->columnSpanFull(),
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
                ->action(fn (SidangRegistration $record) => app(SidangRequirementService::class)->validate($record)),
            Tables\Actions\Action::make('submit')
                ->label('Submit')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (SidangRegistration $record) => in_array($record->status, ['draft', 'revision_required'], true))
                ->action(fn (SidangRegistration $record) => app(SidangRegistrationService::class)->submit($record)),
            Tables\Actions\Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-shield-check')
                ->visible(fn () => auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->label('Catatan')])
                ->action(fn (SidangRegistration $record, array $data) => app(SidangVerificationService::class)->verify($record, $data['note'] ?? null)),
            Tables\Actions\Action::make('waiveManual')
                ->label('Waive Manual')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->visible(fn () => auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->required()])
                ->action(function (SidangRegistration $record, array $data) {
                    app(SidangRequirementService::class)->validate($record);
                    $record->requirementResults()
                        ->whereHas('requirement', fn ($query) => $query->where('requirement_type', 'manual'))
                        ->update(['status' => 'waived', 'note' => $data['note'], 'checked_by' => auth()->id(), 'checked_at' => now()]);
                }),
            Tables\Actions\Action::make('requestRevision')
                ->label('Minta Perbaikan')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => auth()->user()?->can('verify_sidang_registration'))
                ->form([Forms\Components\Textarea::make('note')->required()])
                ->action(fn (SidangRegistration $record, array $data) => app(SidangVerificationService::class)->requestRevision($record, $data['note'])),
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
