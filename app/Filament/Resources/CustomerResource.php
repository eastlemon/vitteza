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

    public static function getNavigationLabel(): string
    {
        return __('customers.nav');
    }

    public static function getModelLabel(): string
    {
        return __('customers.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('customers.models');
    }

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('customers.section.main'))
                ->schema([
                    TextInput::make('name')->label(__('customers.fields.name'))->required()->maxLength(180),
                    TextInput::make('full_legal_name')->label(__('customers.fields.full_legal_name'))->maxLength(255),
                    TextInput::make('inn')->label(__('customers.fields.inn'))->maxLength(15),
                    TextInput::make('kpp')->label(__('customers.fields.kpp'))->maxLength(10),
                    TextInput::make('ogrn')->label(__('customers.fields.ogrn'))->maxLength(15),
                    TextInput::make('contact_person')->label(__('customers.fields.contact_person'))->maxLength(120),
                    TextInput::make('contact_phone')->label(__('common.phone'))->maxLength(20),
                    TextInput::make('contact_email')->label('E-mail')->email()->maxLength(120),
                    Checkbox::make('is_active')->label(__('common.active'))->default(true),
                ])->columns(2),

            Section::make(__('customers.section.requisites'))
                ->schema([
                    Textarea::make('legal_address')->label(__('customers.fields.legal_address'))->rows(2),
                    Textarea::make('postal_address')->label(__('customers.fields.postal_address'))->rows(2),
                    TextInput::make('account_number')->label(__('customers.fields.account_number'))->maxLength(30),
                    TextInput::make('bank_name')->label(__('customers.fields.bank_name'))->maxLength(180),
                    TextInput::make('bank_bik')->label(__('customers.fields.bank_bik'))->maxLength(10),
                    TextInput::make('correspondent_account')->label(__('customers.fields.correspondent_account'))->maxLength(30),
                ])->columns(2),

            Section::make(__('customers.section.contracts'))
                ->schema([
                    Repeater::make('contracts')
                        ->label('')
                        ->relationship('contracts')
                        ->schema([
                            TextInput::make('contract_no')->label(__('common.contract_no'))->required()->maxLength(80),
                            DatePicker::make('signed_at')->label(__('customers.fields.signed_at')),
                            DatePicker::make('expires_at')->label(__('customers.fields.expires_at')),
                            TextInput::make('subject')->label(__('customers.fields.subject'))->maxLength(255),
                            Checkbox::make('is_active')->label(__('customers.fields.contract_active'))->default(true),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->defaultItems(0),
                ]),

            Section::make(__('common.notes'))
                ->schema([
                    Textarea::make('comment')->label(__('common.comment'))->rows(2),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('customers.cols.name'))->searchable()->sortable(),
                TextColumn::make('active_contract')
                    ->label(__('common.contract_no'))
                    ->state(fn (Customer $record) => $record->contracts()->where('is_active', true)->value('contract_no'))
                    ->searchable(),
                TextColumn::make('inn')->label(__('customers.fields.inn')),
                TextColumn::make('contact_person')->label(__('customers.cols.contact'))->toggleable(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
