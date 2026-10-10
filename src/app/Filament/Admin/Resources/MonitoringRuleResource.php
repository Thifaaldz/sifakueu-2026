<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MonitoringRuleResource\Pages;
use App\Filament\Concerns\AppliesSifakResourceScope;
use App\Models\MonitoringRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringRuleResource extends Resource
{
    use AppliesSifakResourceScope;

    protected static ?string $model = MonitoringRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'M3 Monitoring';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->maxLength(80),
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\Select::make('domain')->required()->options([
                'ACADEMIC' => 'Academic',
                'KRS' => 'KRS',
                'STUDY_PROGRESS' => 'Study Progress',
                'TA' => 'TA',
                'SIDANG' => 'Sidang',
                'CPL_PLO' => 'CPL/PLO',
                'REVISION' => 'Revision',
            ]),
            Forms\Components\TextInput::make('source_module')->label('Source')->maxLength(30),
            Forms\Components\TextInput::make('metric_key')->required()->maxLength(100),
            Forms\Components\Select::make('operator')->required()->options([
                '<' => '<',
                '<=' => '<=',
                '>' => '>',
                '>=' => '>=',
                '=' => '=',
                'IN' => 'IN',
                'NOT_IN' => 'NOT IN',
                'DAYS_SINCE' => 'Days Since',
            ]),
            Forms\Components\TextInput::make('warning_value')->numeric()->label('Warning'),
            Forms\Components\TextInput::make('threshold_value')->numeric()->label('Critical'),
            Forms\Components\Select::make('severity')->required()->default('medium')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),
            Forms\Components\TextInput::make('priority')->numeric()->default(50),
            Forms\Components\TextInput::make('version')->numeric()->default(1),
            Forms\Components\Toggle::make('active')->default(true),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\KeyValue::make('metadata')->columnSpanFull(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('domain')->badge()->sortable(),
                Tables\Columns\TextColumn::make('source_module')->badge(),
                Tables\Columns\TextColumn::make('metric_key')->toggleable(),
                Tables\Columns\TextColumn::make('warning_value')->label('Warning'),
                Tables\Columns\TextColumn::make('threshold_value')->label('Critical'),
                Tables\Columns\TextColumn::make('severity')->badge()->color(fn (?string $state): string => match ($state) {
                    'high' => 'danger',
                    'medium' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringRules::route('/'),
            'create' => Pages\CreateMonitoringRule::route('/create'),
            'edit' => Pages\EditMonitoringRule::route('/{record}/edit'),
        ];
    }
}
