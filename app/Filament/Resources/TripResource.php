<?php

namespace App\Filament\Resources;

use App\Enums\TripStatus;
use App\Filament\Resources\TripResource\Pages;
use App\Models\Cargo;
use App\Models\CargoTemplate;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Рейсы';

    protected static ?string $modelLabel = 'Рейс';

    protected static ?string $pluralModelLabel = 'Рейсы';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $canFinance = $user && ($user->isAdmin() || $user->hasCompetency('finance.view'));

        return $schema->components([
            Section::make('Рейс')
                ->schema([
                    DatePicker::make('load_date')->label('Дата погрузки')->required(),
                    DatePicker::make('unload_date')->label('Дата выгрузки'),
                    TextInput::make('route')->label('Маршрут')->required()->maxLength(255)
                        ->placeholder('Москва — Санкт-Петербург'),
                    Select::make('logistic_id')->label('Логист')
                        ->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->required(),
                    Select::make('driver_id')->label('Водитель')
                        ->options(fn () => Driver::where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))
                        ->searchable(),
                    Select::make('customer_id')->label('Заказчик')
                        ->options(fn () => Customer::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                ])->columns(2),

            Section::make('Груз')
                ->schema([
                    Select::make('cargo_template_id')->label('Шаблон груза (подставить параметры)')
                        ->options(fn () => CargoTemplate::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->helperText('При выборе — параметры копируются в экземпляр груза рейса (§14.5).'),
                    TextInput::make('weight_t')->label('Вес, т')->numeric()->step('0.01'),
                    TextInput::make('volume_m3')->label('Объём, м³')->numeric()->step('0.01'),
                ])->columns(3),

            Section::make('Документы и оплата')
                ->schema([
                    TextInput::make('contract_no')->label('Номер договора')->maxLength(80),
                    TextInput::make('cost')->label('Стоимость, ₽')->numeric()->step('0.01')->default(0)
                        ->visible($canFinance),
                    TextInput::make('act_no')->label('Номер акта')->maxLength(80),
                    DatePicker::make('sent_electronic_at')->label('Отправлено электронно'),
                    DatePicker::make('sent_post_at')->label('Отправлено по почте'),
                    Checkbox::make('delivered')->label('Вручено заказчику')->default(false),
                    DatePicker::make('payment_due_date')->label('Срок оплаты')
                        ->visible($canFinance),
                    DatePicker::make('payment_date')->label('Дата оплаты')
                        ->visible($canFinance),
                    TextInput::make('payment_fact')->label('Оплата факт, ₽')->numeric()->step('0.01')->default(0)
                        ->visible($canFinance),
                ])->columns(3),

            Section::make('Статус и доход')
                ->schema([
                    Select::make('status')->label('Статус')
                        ->options(TripStatus::options())
                        ->default('new')
                        ->required()
                        ->helperText('При переходе в статус «Оплачен» доход начисляется в дашборд логиста (§10.5).'),
                    TextInput::make('income_total')->label('Доход компании, ₽')->disabled()->visible($canFinance),
                    TextInput::make('income_logist')->label('Доход логиста, ₽')->disabled()->visible($canFinance),
                    Checkbox::make('paid_to_logist')->label('Доход начислен')->disabled(),
                    Textarea::make('comment')->label('Комментарий')->rows(2)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('load_date')->label('Погрузка')->date('d.m.Y')->sortable(),
                TextColumn::make('logistic.name')->label('Логист')->sortable()->searchable(),
                TextColumn::make('route')->label('Маршрут')->searchable()->limit(24),
                TextColumn::make('customer.name')->label('Заказчик')->sortable()->toggleable(),
                TextColumn::make('driver.full_name')->label('Водитель')->toggleable(),
                TextColumn::make('cost')->label('Стоимость, ₽')->money('RUB')->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'info' => 'confirmed',
                        'warning' => 'in_progress',
                        'primary' => 'completed',
                        'primary' => 'docs_sent',
                        'warning' => 'await_pay',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => TripStatus::labels()[$state] ?? $state),
                TextColumn::make('income_logist')->label('Доход логиста, ₽')->money('RUB')->toggleable(),
                TextColumn::make('unload_date')->label('Выгрузка')->date('d.m.Y')->toggleable(),
            ])
            ->defaultSort('load_date', 'desc')
            ->filters([
                SelectFilter::make('logistic_id')->label('Логист')
                    ->options(fn () => User::where('is_active', true)->pluck('name', 'id')),
                SelectFilter::make('driver_id')->label('Водитель')
                    ->options(fn () => Driver::pluck('full_name', 'id')),
                SelectFilter::make('customer_id')->label('Заказчик')
                    ->options(fn () => Customer::pluck('name', 'id')),
                SelectFilter::make('status')->label('Статус')->options(TripStatus::options()),
                Filter::make('load_date')->label('Период погрузки')
                    ->form([
                        DatePicker::make('from')->label('С'),
                        DatePicker::make('until')->label('По'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('load_date', '>=', $v))
                        ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('load_date', '<=', $v))),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    /**
     * Видимость рейсов (§8.3): админ / trips.view_all — все; иначе — только свои.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
        if (! $user) {
            return $query->whereRaw('1=0');
        }
        if ($user->isAdmin() || $user->hasCompetency('trips.view_all')) {
            return $query;
        }

        return $query->where('logistic_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
