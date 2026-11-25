<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationLabel(): string
    {
        return __('drivers.nav');
    }

    public static function getModelLabel(): string
    {
        return __('drivers.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('drivers.models');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();

        return $schema->components([
            Section::make(__('drivers.section.main'))
                ->schema([
                    TextInput::make('full_name')->label(__('common.full_name'))->required()->maxLength(180),
                    TextInput::make('plate_number')->label(__('drivers.fields.plate_number'))->maxLength(20),
                    TextInput::make('phone')->label(__('common.phone'))->maxLength(20),
                    TextInput::make('vehicle_cert_no')->label(__('drivers.fields.vehicle_cert_no'))->maxLength(40),
                    TextInput::make('vehicle_owner')->label(__('drivers.fields.vehicle_owner'))->maxLength(180),
                    TextInput::make('capacity_t')->label(__('drivers.fields.capacity_t'))->numeric()->step('0.01'),
                    TextInput::make('volume_m3')->label(__('drivers.fields.volume_m3'))->numeric()->step('0.01'),
                    TextInput::make('body_params')->label(__('drivers.fields.body_params'))->maxLength(120),
                    DatePicker::make('license_expiry')->label(__('drivers.fields.license_expiry')),
                    Checkbox::make('is_active')->label(__('common.active'))->default(true),
                ])->columns(2),

            Section::make(__('drivers.section.docs'))
                ->schema([
                    Textarea::make('passport_data')->label(__('drivers.fields.passport_data'))->rows(2),
                    TextInput::make('license_series_no')->label(__('drivers.fields.license_series_no'))->maxLength(40),
                    Textarea::make('ip_requisites')->label(__('drivers.fields.ip_requisites'))->rows(2),
                ])
                ->columns(1)
                ->visible(fn () => $user && ($user->isAdmin() || $user->hasCompetency('drivers.view_sensitive'))),

            Section::make(__('common.notes'))
                ->schema([
                    Textarea::make('comment')->label(__('drivers.fields.comment'))->rows(2),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label(__('common.full_name'))->searchable()->sortable(),
                TextColumn::make('plate_number')->label(__('drivers.cols.plate_number'))->searchable(),
                TextColumn::make('phone')->label(__('common.phone')),
                TextColumn::make('capacity_t')->label(__('drivers.cols.capacity_t'))->sortable(),
                TextColumn::make('volume_m3')->label(__('drivers.cols.volume_m3'))->sortable(),
                TextColumn::make('body_params')->label(__('drivers.cols.body_params'))->toggleable(),
                BooleanColumn::make('is_active')->label(__('common.active')),
            ])
            ->defaultSort('full_name')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
