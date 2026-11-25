<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Mailbox;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('employees.nav');
    }

    public static function getModelLabel(): string
    {
        return __('employees.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('employees.models');
    }

    protected static ?int $navigationSort = 7;

    /** Only the administrator can see the "Employees" section (Spec §8.4). */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    /**
     * The User model has no policy, so without an explicit override Filament
     * in non-strict mode would allow creation by everyone. The section is administrative —
     * only the administrator can create employees.
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('employees.section.main'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('common.full_name'))
                        ->required()
                        ->maxLength(180),
                    TextInput::make('username')
                        ->label(__('common.login'))
                        ->required()
                        ->alphaNum()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->live()
                        ->dehydrateStateUsing(fn ($state) => strtolower($state)),
                    Placeholder::make('derived_email')
                        ->label(__('employees.fields.login_hint'))
                        ->content(function ($get) {
                            $u = $get('username');
                            return $u ? strtolower($u).'@vitteza.ru' : __('employees.fill_login_first');
                        }),
                    Select::make('role')
                        ->label(__('common.role'))
                        ->options([
                            'admin' => __('employees.roles.admin'),
                            'user' => __('employees.roles.user'),
                        ])
                        ->default('user')
                        ->required(),
                    CheckboxList::make('competencies')
                        ->label(__('employees.fields.competences'))
                        ->options([
                            'trips.view' => __('employees.comps.trips_view'),
                            'trips.view_all' => __('employees.comps.trips_view_all'),
                            'trips.manage' => __('employees.comps.trips_manage'),
                            'drivers.view' => __('employees.comps.drivers_view'),
                            'drivers.view_sensitive' => __('employees.comps.drivers_view_sensitive'),
                            'drivers.manage' => __('employees.comps.drivers_manage'),
                            'customers.view' => __('employees.comps.customers_view'),
                            'customers.manage' => __('employees.comps.customers_manage'),
                            'cargo.view' => __('employees.comps.cargo_view'),
                            'cargo.manage' => __('employees.comps.cargo_manage'),
                            'logists.view' => __('employees.comps.logists_view'),
                            'finance.view' => __('employees.comps.finance_view'),
                        ])
                        ->columns(2)
                        ->default([]),
                ])->columns(2),

            Section::make(__('employees.section.access'))
                ->schema([
                    TextInput::make('password')
                        ->label(__('common.password'))
                        ->password()
                        ->revealable()
                        ->required(fn ($context) => $context === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->maxLength(255)
                        ->helperText(__('employees.helpers.one_password')),
                    TextInput::make('quota_mb')
                        ->label(__('employees.fields.mail_quota'))
                        ->numeric()
                        ->default(2048)
                        ->minValue(256)
                        ->maxValue(10240),
                    Checkbox::make('create_mailbox')
                        ->label(__('employees.fields.create_mailbox'))
                        ->default(true)
                        ->visible(fn ($context) => $context === 'create'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('common.full_name'))->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('username')->label(__('common.login')),
                TextColumn::make('role')
                    ->label(__('common.role'))
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'primary' => 'user',
                    ]),
                BooleanColumn::make('is_active')->label(__('common.active')),
            ])
            ->actions([
                EditAction::make(),
                Action::make('block')
                    ->label(__('employees.cols.blocked'))
                    ->icon('heroicon-o-lock-closed')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => static::toggleBlock($record)),
            ]);
    }

    public static function toggleBlock(User $user): void
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        Mailbox::where('user_id', $user->id)->update(['is_active' => $user->is_active]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
