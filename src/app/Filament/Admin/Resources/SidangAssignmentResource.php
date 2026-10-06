<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangAssignmentResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Dosen;
use App\Models\SidangAssignment;
use App\Services\Sifak\SidangAssignmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangAssignmentResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangAssignment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('dosen_id')->relationship('dosen', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('role')->required()->options([
                'PEMBIMBING_1' => 'Pembimbing 1',
                'PEMBIMBING_2' => 'Pembimbing 2',
                'PENGUJI_1' => 'Penguji 1',
                'PENGUJI_2' => 'Penguji 2',
                'KETUA_SIDANG' => 'Ketua Sidang',
                'SEKRETARIS' => 'Sekretaris',
            ]),
            Forms\Components\Select::make('status')->default('assigned')->options(['assigned' => 'Assigned', 'confirmed' => 'Confirmed', 'declined' => 'Declined']),
            Forms\Components\Textarea::make('justification')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('dosen.name')->searchable(),
            Tables\Columns\TextColumn::make('role')->badge(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('assign')
                ->label('Set Assignment')
                ->icon('heroicon-o-check')
                ->visible(fn () => auth()->user()?->can('manage_sidang_assignment') || auth()->user()?->can('update_sidang::assignment'))
                ->action(fn (SidangAssignment $record) => app(SidangAssignmentService::class)->assign($record->registration, Dosen::findOrFail($record->dosen_id), $record->role, $record->justification, $record->recommendation_id)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangAssignments::route('/'),
            'create' => Pages\CreateSidangAssignment::route('/create'),
            'edit' => Pages\EditSidangAssignment::route('/{record}/edit'),
        ];
    }
}
