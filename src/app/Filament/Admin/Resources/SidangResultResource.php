<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SidangResultResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\SidangResult;
use App\Services\Sifak\SidangScoringService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SidangResultResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = SidangResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'M1 Sidang';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sidang_registration_id')->relationship('registration', 'registration_number')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('final_score')->numeric()->default(0),
            Forms\Components\TextInput::make('final_grade'),
            Forms\Components\Select::make('decision')->required()->options([
                'lulus' => 'Lulus',
                'lulus_dengan_revisi' => 'Lulus dengan Revisi',
                'mengulang' => 'Mengulang',
                'tidak_lulus' => 'Tidak Lulus',
                'ditunda' => 'Ditunda',
            ]),
            Forms\Components\Textarea::make('decision_note')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('published_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('registration.registration_number')->searchable(),
            Tables\Columns\TextColumn::make('registration.mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('final_score')->badge(),
            Tables\Columns\TextColumn::make('final_grade')->badge(),
            Tables\Columns\TextColumn::make('decision')->badge(),
            Tables\Columns\TextColumn::make('published_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-megaphone')
                ->visible(fn (SidangResult $record) => ! $record->published_at && auth()->user()?->can('publish_sidang_result'))
                ->action(fn (SidangResult $record) => app(SidangScoringService::class)->publish($record)),
            Tables\Actions\EditAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSidangResults::route('/'),
            'create' => Pages\CreateSidangResult::route('/create'),
            'edit' => Pages\EditSidangResult::route('/{record}/edit'),
        ];
    }
}
