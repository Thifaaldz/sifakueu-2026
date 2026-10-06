<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangRevisionResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangRevision;
use App\Services\Sifak\SidangRevisionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangRevisionResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangRevision::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('examiner_id')->relationship('examiner', 'name')->searchable()->preload(),
            Forms\Components\Textarea::make('description')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('category'),
            Forms\Components\DatePicker::make('deadline'),
            Forms\Components\Select::make('status')->default('open')->options([
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'submitted' => 'Submitted',
                'validated' => 'Validated',
                'rejected' => 'Rejected',
                'closed' => 'Closed',
            ]),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('examiner.name')->label('Penguji')->searchable(),
            Tables\Columns\TextColumn::make('description')->limit(60),
            Tables\Columns\TextColumn::make('deadline')->date(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([
            Tables\Actions\Action::make('validateRevision')
                ->label('Validasi')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (SidangRevision $record) => ! in_array($record->status, ['validated', 'closed'], true) && (auth()->user()?->can('input_sidang_revision') || auth()->user()?->can('update_sidang::revision')))
                ->action(fn (SidangRevision $record) => app(SidangRevisionService::class)->validate($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangRevisions::route('/'),
            'create' => Pages\CreateSidangRevision::route('/create'),
            'edit' => Pages\EditSidangRevision::route('/{record}/edit'),
        ];
    }
}
