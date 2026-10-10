<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MahasiswaPortfolioResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MahasiswaPortfolio;
use App\Filament\Support\MahasiswaOwnership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MahasiswaPortfolioResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MahasiswaPortfolio::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'M6 Profiling Mahasiswa';
    protected static ?string $navigationLabel = 'Portofolio Mahasiswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            MahasiswaOwnership::field(),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\TextInput::make('category')->label('Kategori')->default('project')->maxLength(80),
            Forms\Components\TextInput::make('url')->label('URL')->url()->maxLength(255),
            Forms\Components\TagsInput::make('skills_json')->label('Skill'),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('mahasiswa.nim')->label('NIM')->searchable(),
            Tables\Columns\TextColumn::make('mahasiswa.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->label('Kategori')->badge(),
            Tables\Columns\TextColumn::make('url')->label('URL')->limit(35)->url(fn (MahasiswaPortfolio $record) => $record->url),
            Tables\Columns\TextColumn::make('skills_json')
                ->label('Skill')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMahasiswaPortfolios::route('/'), 'create' => Pages\CreateMahasiswaPortfolio::route('/create'), 'edit' => Pages\EditMahasiswaPortfolio::route('/{record}/edit')];
    }
}
