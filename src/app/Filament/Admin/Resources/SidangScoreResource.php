<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangScoreResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\Dosen;
use App\Models\SidangScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangScoreResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\Select::make('examiner_id')
                ->relationship('examiner', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('dosen_penguji') && auth()->user()?->dosen?->id ? $query->whereKey(auth()->user()->dosen->id) : $query)
                ->default(fn () => auth()->user()?->dosen?->id)
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('sidang_rubric_id')->relationship('rubric', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('score')->numeric()->minValue(0)->maxValue(100)->required(),
            Forms\Components\Textarea::make('note')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('examiner.name')->label('Penguji')->searchable(),
            Tables\Columns\TextColumn::make('rubric.name')->label('Rubrik')->searchable(),
            Tables\Columns\TextColumn::make('score')->badge(),
            Tables\Columns\TextColumn::make('submitted_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('stampSubmit')
                ->label('Submit')
                ->icon('heroicon-o-check')
                ->visible(fn (SidangScore $record) => ! $record->submitted_at && (auth()->user()?->can('input_sidang_score') || auth()->user()?->can('update_sidang::score')))
                ->action(fn (SidangScore $record) => $record->update(['submitted_at' => now(), 'examiner_id' => $record->examiner_id ?: Dosen::where('user_id', auth()->id())->value('id')])),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangScores::route('/'),
            'create' => Pages\CreateSidangScore::route('/create'),
            'edit' => Pages\EditSidangScore::route('/{record}/edit'),
        ];
    }
}
