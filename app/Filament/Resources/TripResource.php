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

    public static function getNavigationLabel(): string
    {
        return __('trips.nav');
    }

    public static function getModelLabel(): string
    {
        return __('trips.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('trips.models');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $canFinance = $user && ($user->isAdmin() || $user->hasCompetency('finance.view'));

        return $schema->components([
            Section::make(__('trips.section.trip'))
                ->schema([
                    DatePicker::make('load_date')->label(__('trips.fields.load_date'))->required(),
                    DatePicker::make('unload_date')->label(__('trips.fields.unload_date')),
                    TextInput::make('route')->label(__('common.route'))->required()->maxLength(255)
                        ->placeholder(__('trips.placeholder.route')),
                    Select::make('logistic_id')->label(__('common.logistic'))
                        ->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->required(),
                    Select::make('driver_id')->label(__('common.driver'))
                        ->options(fn () => Driver::where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))
                        ->searchable(),
                    Select::make('customer_id')->label(__('common.customer'))
                        ->options(fn () => Customer::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                ])->columns(2),

            Section::make(__('trips.section.cargo'))
                ->schema([
                    Select::make('cargo_template_id')->label(__('trips.fields.cargo_template'))
                        ->options(fn () => CargoTemplate::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->helperText(__('trips.helpers.cargo_template')),
                    TextInput::make('weight_t')->label(__('common.weight_t'))->numeric()->step('0.01'),
                    TextInput::make('volume_m3')->label(__('common.volume_m3'))->numeric()->step('0.01'),
                ])->columns(3),

            Section::make(__('trips.section.docs'))
                ->schema([
                    TextInput::make('contract_no')->label(__('common.contract_no'))->maxLength(80),
                    TextInput::make('cost')->label(__('common.cost'))->numeric()->step('0.01')->default(0)
                        ->visible($canFinance),
                    TextInput::make('act_no')->label(__('trips.fields.act_no'))->maxLength(80),
                    DatePicker::make('sent_electronic_at')->label(__('trips.fields.sent_electronic_at')),
                    DatePicker::make('sent_post_at')->label(__('trips.fields.sent_post_at')),
                    Checkbox::make('delivered')->label(__('trips.fields.delivered'))->default(false),
                    DatePicker::make('payment_due_date')->label(__('trips.fields.payment_due_date'))
                        ->visible($canFinance),
                    DatePicker::make('payment_date')->label(__('trips.fields.payment_date'))
                        ->visible($canFinance),
                    TextInput::make('payment_fact')->label(__('trips.fields.payment_fact'))->numeric()->step('0.01')->default(0)
                        ->visible($canFinance),
                ])->columns(3),

            Section::make(__('trips.section.status'))
                ->schema([
                    Select::make('status')->label(__('common.status'))
                        ->options(TripStatus::options())
                        ->default('new')
                        ->required()
                        ->helperText(__('trips.helpers.status')),
                    TextInput::make('income_total')->label(__('common.income_total'))->disabled()->visible($canFinance),
                    TextInput::make('income_logist')->label(__('common.income_logist'))->disabled()->visible($canFinance),
                    Checkbox::make('paid_to_logist')->label(__('trips.fields.paid_to_logist'))->disabled(),
                    Textarea::make('comment')->label(__('common.comment'))->rows(2)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('load_date')->label(__('trips.cols.load_date'))->date('d.m.Y')->sortable(),
                TextColumn::make('logistic.name')->label(__('common.logistic'))->sortable()->searchable(),
                TextColumn::make('route')->label(__('common.route'))->searchable()->limit(24),
                TextColumn::make('customer.name')->label(__('common.customer'))->sortable()->toggleable(),
                TextColumn::make('driver.full_name')->label(__('common.driver'))->toggleable(),
                TextColumn::make('cost')->label(__('common.cost'))->money('RUB')->sortable(),
                TextColumn::make('status')
                    ->label(__('common.status'))
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
                TextColumn::make('income_logist')->label(__('common.income_logist'))->money('RUB')->toggleable(),
                TextColumn::make('unload_date')->label(__('trips.cols.unload_date'))->date('d.m.Y')->toggleable(),
            ])
            ->defaultSort('load_date', 'desc')
            ->filters([
                SelectFilter::make('logistic_id')->label(__('common.logistic'))
                    ->options(fn () => User::where('is_active', true)->pluck('name', 'id')),
                SelectFilter::make('driver_id')->label(__('common.driver'))
                    ->options(fn () => Driver::pluck('full_name', 'id')),
                SelectFilter::make('customer_id')->label(__('common.customer'))
                    ->options(fn () => Customer::pluck('name', 'id')),
                SelectFilter::make('status')->label(__('common.status'))->options(TripStatus::options()),
                Filter::make('load_date')->label(__('trips.filters.load_period'))
                    ->form([
                        DatePicker::make('from')->label(__('common.from')),
                        DatePicker::make('until')->label(__('common.until')),
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
     * Trip visibility (Spec §8.3): admin / trips.view_all — all trips; otherwise — own only.
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
