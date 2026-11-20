<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CargoTemplateResource\Pages;
use App\Models\CargoTemplate;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CargoTemplateResource extends Resource
{
    protected static ?string $model = CargoTemplate::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Грузы';

    protected static ?string $modelLabel = 'Шаблон груза';

    protected static ?string $pluralModelLabel = 'Грузы (шаблоны)';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Шаблон груза')
                ->schema([
                    TextInput::make('name')->label('Наименование груза')->required()->maxLength(180),
                    Select::make('cargo_class')->label('Класс груза')
                        ->options([
                            'general' => 'Обычный',
                            'dangerous' => 'Опасный',
                            'perishable' => 'Скоропортящийся',
                            'oversized' => 'Негабаритный',
                            'liquid' => 'Наливной',
                            'bulk' => 'Насыпной',
                        ])
                        ->default('general')
                        ->required(),
                    Textarea::make('description')->label('Описание')->rows(2),
                    TextInput::make('default_weight_t')->label('Типовой вес, т')->numeric()->step('0.01'),
                    TextInput::make('default_volume_m3')->label('Типовой объём, м³')->numeric()->step('0.01'),
                    TextInput::make('default_packaging')->label('Типовая упаковка')->maxLength(80),
                    TextInput::make('default_pieces')->label('Типовое кол-во мест')->numeric(),
                    TextInput::make('default_dimensions')->label('Габариты Д×Ш×В, м')->maxLength(60),
                    TextInput::make('temperature_regime')->label('Температурный режим')->maxLength(60),
                    Textarea::make('special_conditions')->label('Особые условия перевозки')->rows(2),
                    TextInput::make('declared_value')->label('Объявленная ценность, ₽')->numeric()->step('0.01'),
                    Checkbox::make('is_active')->label('Активен')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Груз')->searchable()->sortable(),
                TextColumn::make('cargo_class')
                    ->label('Класс')
                    ->badge()
                    ->colors([
                        'danger' => 'dangerous',
                        'warning' => 'perishable',
                        'primary' => 'liquid',
                        'gray' => 'general',
                    ]),
                TextColumn::make('default_weight_t')->label('Вес, т')->toggleable(),
                TextColumn::make('default_volume_m3')->label('Объём, м³')->toggleable(),
                BooleanColumn::make('is_active')->label('Активен'),
            ])
            ->defaultSort('name')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCargoTemplates::route('/'),
            'create' => Pages\CreateCargoTemplate::route('/create'),
            'edit' => Pages\EditCargoTemplate::route('/{record}/edit'),
        ];
    }
}
