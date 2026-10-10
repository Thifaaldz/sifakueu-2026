<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Alert;
use App\Services\Sifak\MonitoringAlertService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = Alert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('mahasiswa_id')->relationship('mahasiswa', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('monitoring_snapshot_id')->relationship('snapshot', 'id')->label('Snapshot')->searchable()->preload(),
            Forms\Components\Select::make('rule_id')->relationship('rule', 'name')->label('Rule')->searchable()->preload(),
            Forms\Components\TextInput::make('type')->label('Tipe')->required()->maxLength(100),
            Forms\Components\TextInput::make('alert_type')->label('Tipe Alert')->maxLength(100),
            Forms\Components\Select::make('severity')->required()->default('medium')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),
            Forms\Components\Select::make('risk_status')->label('Risiko')->options(['green' => 'Hijau', 'yellow' => 'Kuning', 'red' => 'Merah']),
            Forms\Components\Select::make('status')->required()->default('open')->options([
                'open' => 'Open',
                'acknowledged' => 'Diakui',
                'in_follow_up' => 'Dalam follow-up',
                'escalated' => 'Eskalasi',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
                'followed_up' => 'Ditindaklanjuti',
            ]),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\Select::make('assigned_to')->relationship('assignee', 'name')->label('Ditugaskan ke')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('acknowledged_at')->label('Diakui pada'),
            Forms\Components\DateTimePicker::make('resolved_at')->label('Resolved pada'),
            Forms\Components\DateTimePicker::make('escalated_at')->label('Eskalasi pada'),
            Forms\Components\DateTimePicker::make('due_at')->label('Batas tindak lanjut'),
            Forms\Components\KeyValue::make('payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('rule.domain')->label('Domain')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge()->searchable(),
                Tables\Columns\TextColumn::make('risk_status')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'green' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('acknowledge')
                    ->label('Acknowledge')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (Alert $record) => $record->status === 'open' && (
                        auth()->user()?->can('acknowledge_own_alert') || auth()->user()?->can('update_alert')
                    ))
                    ->action(fn (Alert $record) => app(MonitoringAlertService::class)->acknowledge($record, auth()->id())),
                Tables\Actions\Action::make('followUp')
                    ->label('Tindak lanjut')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Alert $record) => in_array($record->status, ['open', 'acknowledged', 'escalated'], true) && (
                        auth()->user()?->can('create_alert_followup') || auth()->user()?->can('followup_student_alert') || auth()->user()?->can('update_alert')
                    ))
                    ->form([
                        Forms\Components\Select::make('action_type')->label('Jenis')->default('follow_up')->options([
                            'follow_up' => 'Follow-up',
                            'counseling' => 'Konseling akademik',
                            'reminder' => 'Reminder',
                            'coordination' => 'Koordinasi',
                        ])->required(),
                        Forms\Components\Textarea::make('note')->label('Catatan')->required(),
                        Forms\Components\DatePicker::make('next_action_date')->label('Aksi berikutnya'),
                    ])
                    ->action(fn (Alert $record, array $data) => app(MonitoringAlertService::class)->followUp(
                        $record,
                        $data['note'],
                        $data['action_type'],
                        $data['next_action_date'] ?? null,
                        auth()->id()
                    )),
                Tables\Actions\Action::make('escalate')
                    ->label('Eskalasi')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->visible(fn (Alert $record) => ! $record->escalated_at && (
                        auth()->user()?->can('escalate_alert') || auth()->user()?->can('update_alert')
                    ))
                    ->form([
                        Forms\Components\Select::make('to_role')->label('Tujuan')->options([
                            'kaprodi' => 'Kaprodi',
                            'admin_prodi' => 'Admin Prodi',
                            'dekan' => 'Dekan',
                            'wd' => 'Wakil Dekan',
                        ])->required(),
                        Forms\Components\Textarea::make('reason')->label('Alasan')->required(),
                    ])
                    ->action(fn (Alert $record, array $data) => app(MonitoringAlertService::class)->escalate(
                        $record,
                        $data['to_role'],
                        $data['reason'],
                        auth()->user()?->roles?->first()?->name,
                        auth()->id()
                    )),
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Alert $record) => ! in_array($record->status, ['resolved', 'closed'], true) && (
                        auth()->user()?->can('resolve_alert') || auth()->user()?->can('update_alert')
                    ))
                    ->action(fn (Alert $record) => app(MonitoringAlertService::class)->resolve($record, auth()->id())),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit' => Pages\EditAlert::route('/{record}/edit'),
        ];
    }
}
