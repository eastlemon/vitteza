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

    protected static ?string $navigationLabel = 'Сотрудники';

    protected static ?string $modelLabel = 'Сотрудник';

    protected static ?string $pluralModelLabel = 'Сотрудники';

    protected static ?int $navigationSort = 7;

    /** Только администратор видит раздел «Сотрудники» (ТЗ §8.4). */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->schema([
                    TextInput::make('name')
                        ->label('ФИО')
                        ->required()
                        ->maxLength(180),
                    TextInput::make('username')
                        ->label('Логин')
                        ->required()
                        ->alphaNum()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->live()
                        ->dehydrateStateUsing(fn ($state) => strtolower($state)),
                    Placeholder::make('derived_email')
                        ->label('Логин для входа (система и почта)')
                        ->content(function ($get) {
                            $u = $get('username');
                            return $u ? strtolower($u).'@vitteza.ru' : '— заполните логин —';
                        }),
                    Select::make('role')
                        ->label('Роль')
                        ->options([
                            'admin' => 'Администратор',
                            'user' => 'Пользователь',
                        ])
                        ->default('user')
                        ->required(),
                    CheckboxList::make('competencies')
                        ->label('Компетенции (для роли «Пользователь»)')
                        ->options([
                            'trips.view' => 'Рейсы: просмотр',
                            'trips.view_all' => 'Рейсы: видеть все (а не только свои)',
                            'trips.manage' => 'Рейсы: управление',
                            'drivers.view' => 'Водители: просмотр',
                            'drivers.view_sensitive' => 'Водители: чувствительные поля',
                            'drivers.manage' => 'Водители: управление',
                            'customers.view' => 'Заказчики: просмотр',
                            'customers.manage' => 'Заказчики: управление',
                            'cargo.view' => 'Грузы: просмотр',
                            'cargo.manage' => 'Грузы: управление',
                            'logists.view' => 'Логисты: просмотр',
                            'finance.view' => 'Финансы: просмотр',
                        ])
                        ->columns(2)
                        ->default([]),
                ])->columns(2),

            Section::make('Доступ и почта')
                ->schema([
                    TextInput::make('password')
                        ->label('Пароль')
                        ->password()
                        ->revealable()
                        ->required(fn ($context) => $context === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->maxLength(255)
                        ->helperText('Один пароль для входа в систему и для почты.'),
                    TextInput::make('quota_mb')
                        ->label('Квота почтового ящика, MB')
                        ->numeric()
                        ->default(2048)
                        ->minValue(256)
                        ->maxValue(10240),
                    Checkbox::make('create_mailbox')
                        ->label('Создать почтовый ящик @vitteza.ru')
                        ->default(true)
                        ->visible(fn ($context) => $context === 'create'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('ФИО')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('username')->label('Логин'),
                TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'primary' => 'user',
                    ]),
                BooleanColumn::make('is_active')->label('Активен'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('block')
                    ->label('Блокировка')
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
