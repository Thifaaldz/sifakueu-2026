<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaProfileResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaProfile;
use App\Services\Sifak\StudentProfileService;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaProfileResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('academic_score')->numeric()->default(0),
            Forms\Components\TextInput::make('competency_score')->numeric()->default(0),
            Forms\Components\Select::make('profile_status')->options(['draft' => 'Draft', 'calculated' => 'Calculated', 'stale' => 'Stale'])->default('draft'),
            Forms\Components\DateTimePicker::make('last_recalculated_at')->label('Recalculated'),
            Forms\Components\KeyValue::make('summary_payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('academic_score')->label('Academic')->sortable(),
            Tables\Columns\TextColumn::make('competency_score')->label('Competency')->sortable(),
            Tables\Columns\TextColumn::make('profile_status')->badge(),
            Tables\Columns\TextColumn::make('last_recalculated_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\Action::make('recalculate')
                ->label('Recalculate')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => auth()->user()?->can('recalculate_student_profile') || auth()->user()?->can('update_mahasiswa::profile'))
                ->action(fn (MahasiswaProfile $record) => app(StudentProfileService::class)->recalculate($record->mahasiswa)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaProfiles::route('/'), 'create' => Pages\CreateMahasiswaProfile::route('/create'), 'edit' => Pages\EditMahasiswaProfile::route('/{record}/edit')];
    }
}
