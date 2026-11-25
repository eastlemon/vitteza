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

    public static function getNavigationLabel(): string
    {
        return __('cargo.nav');
    }

    public static function getModelLabel(): string
    {
        return __('cargo.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cargo.models');
    }

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('cargo.section.main'))
                ->schema([
                    TextInput::make('name')->label(__('cargo.fields.name'))->required()->maxLength(180),
                    Select::make('cargo_class')->label(__('cargo.fields.cargo_class'))
                        ->options([
                            'general' => __('cargo.classes.general'),
                            'dangerous' => __('cargo.classes.dangerous'),
                            'perishable' => __('cargo.classes.perishable'),
                            'oversized' => __('cargo.classes.oversized'),
                            'liquid' => __('cargo.classes.liquid'),
                            'bulk' => __('cargo.classes.bulk'),
                        ])
                        ->default('general')
                        ->required(),
                    Textarea::make('description')->label(__('cargo.fields.description'))->rows(2),
                    TextInput::make('default_weight_t')->label(__('cargo.fields.default_weight_t'))->numeric()->step('0.01'),
                    TextInput::make('default_volume_m3')->label(__('cargo.fields.default_volume_m3'))->numeric()->step('0.01'),
                    TextInput::make('default_packaging')->label(__('cargo.fields.default_packaging'))->maxLength(80),
                    TextInput::make('default_pieces')->label(__('cargo.fields.default_pieces'))->numeric(),
                    TextInput::make('default_dimensions')->label(__('cargo.fields.default_dimensions'))->maxLength(60),
                    TextInput::make('temperature_regime')->label(__('cargo.fields.temperature_regime'))->maxLength(60),
                    Textarea::make('special_conditions')->label(__('cargo.fields.special_conditions'))->rows(2),
                    TextInput::make('declared_value')->label(__('cargo.fields.declared_value'))->numeric()->step('0.01'),
                    Checkbox::make('is_active')->label(__('common.active'))->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('cargo.cols.name'))->searchable()->sortable(),
                TextColumn::make('cargo_class')
                    ->label(__('cargo.cols.cargo_class'))
                    ->badge()
                    ->colors([
                        'danger' => 'dangerous',
                        'warning' => 'perishable',
                        'primary' => 'liquid',
                        'gray' => 'general',
                    ]),
                TextColumn::make('default_weight_t')->label(__('common.weight_t'))->toggleable(),
                TextColumn::make('default_volume_m3')->label(__('common.volume_m3'))->toggleable(),
                BooleanColumn::make('is_active')->label(__('common.active')),
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
