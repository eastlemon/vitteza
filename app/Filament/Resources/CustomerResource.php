<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Заказчики';

    protected static ?string $modelLabel = 'Заказчик';

    protected static ?string $pluralModelLabel = 'Заказчики';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заказчик')
                ->schema([
                    TextInput::make('name')->label('Наименование организации')->required()->maxLength(180),
                    TextInput::make('full_legal_name')->label('Полное юр. наименование')->maxLength(255),
                    TextInput::make('inn')->label('ИНН')->maxLength(15),
                    TextInput::make('kpp')->label('КПП')->maxLength(10),
                    TextInput::make('ogrn')->label('ОГРН')->maxLength(15),
                    TextInput::make('contact_person')->label('Контактное лицо')->maxLength(120),
                    TextInput::make('contact_phone')->label('Телефон')->maxLength(20),
                    TextInput::make('contact_email')->label('E-mail')->email()->maxLength(120),
                    Checkbox::make('is_active')->label('Активен')->default(true),
                ])->columns(2),

            Section::make('Реквизиты для документов (§13.3)')
                ->schema([
                    Textarea::make('legal_address')->label('Юридический адрес')->rows(2),
                    Textarea::make('postal_address')->label('Почтовый адрес для документов')->rows(2),
                    TextInput::make('account_number')->label('Расчётный счёт')->maxLength(30),
                    TextInput::make('bank_name')->label('Банк')->maxLength(180),
                    TextInput::make('bank_bik')->label('БИК')->maxLength(10),
                    TextInput::make('correspondent_account')->label('Корр. счёт')->maxLength(30),
                ])->columns(2),

            Section::make('Договоры (§13.4)')
                ->schema([
                    Repeater::make('contracts')
                        ->label('')
                        ->relationship('contracts')
                        ->schema([
                            TextInput::make('contract_no')->label('Номер договора')->required()->maxLength(80),
                            DatePicker::make('signed_at')->label('Дата заключения'),
                            DatePicker::make('expires_at')->label('Срок действия'),
                            TextInput::make('subject')->label('Предмет договора')->maxLength(255),
                            Checkbox::make('is_active')->label('Действует')->default(true),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->defaultItems(0),
                ]),

            Section::make('Заметки')
                ->schema([
                    Textarea::make('comment')->label('Комментарий')->rows(2),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Организация')->searchable()->sortable(),
                TextColumn::make('active_contract')
                    ->label('Номер договора')
                    ->state(fn (Customer $record) => $record->contracts()->where('is_active', true)->value('contract_no'))
                    ->searchable(),
                TextColumn::make('inn')->label('ИНН'),
                TextColumn::make('contact_person')->label('Контакт')->toggleable(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
