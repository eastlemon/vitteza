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

    protected static ?string $navigationLabel = 'Водители';

    protected static ?string $modelLabel = 'Водитель';

    protected static ?string $pluralModelLabel = 'Водители';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();

        return $schema->components([
            Section::make('Водитель и транспорт')
                ->schema([
                    TextInput::make('full_name')->label('ФИО')->required()->maxLength(180),
                    TextInput::make('plate_number')->label('Гос. номер машины')->maxLength(20),
                    TextInput::make('phone')->label('Телефон')->maxLength(20),
                    TextInput::make('vehicle_cert_no')->label('Номер свидетельства на авто (СТС)')->maxLength(40),
                    TextInput::make('vehicle_owner')->label('Собственник авто')->maxLength(180),
                    TextInput::make('capacity_t')->label('Тоннаж авто, т')->numeric()->step('0.01'),
                    TextInput::make('volume_m3')->label('Объём кузова, м³')->numeric()->step('0.01'),
                    TextInput::make('body_params')->label('Параметры кузова (Д×Ш×В, тип)')->maxLength(120),
                    DatePicker::make('license_expiry')->label('Срок действия прав'),
                    Checkbox::make('is_active')->label('Активен')->default(true),
                ])->columns(2),

            Section::make('Документы (чувствительные, §11.4)')
                ->schema([
                    Textarea::make('passport_data')->label('Паспортные данные (серия, номер, кем и когда выдан)')->rows(2),
                    TextInput::make('license_series_no')->label('Серия и номер прав')->maxLength(40),
                    Textarea::make('ip_requisites')->label('Реквизиты ИП (ИНН, ОГРНИП, счёт, банк)')->rows(2),
                ])
                ->columns(1)
                ->visible(fn () => $user && ($user->isAdmin() || $user->hasCompetency('drivers.view_sensitive'))),

            Section::make('Заметки')
                ->schema([
                    Textarea::make('comment')->label('Внутренний комментарий')->rows(2),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('plate_number')->label('Гос. номер')->searchable(),
                TextColumn::make('phone')->label('Телефон'),
                TextColumn::make('capacity_t')->label('Тоннаж, т')->sortable(),
                TextColumn::make('volume_m3')->label('Объём, м³')->sortable(),
                TextColumn::make('body_params')->label('Кузов')->toggleable(),
                BooleanColumn::make('is_active')->label('Активен'),
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
