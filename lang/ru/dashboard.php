<?php

declare(strict_types=1);

// Главный дашборд (виджет VittezaDashboardWidget + blade-шаблон).

return [
    'title' => 'Дашборд',
    'monthly_reset' => 'ежемесячное обнуление счётчиков (§15.3)',

    'stats' => [
        'trips_count' => 'Число рейсов',
        'total_cost' => 'Стоимость рейсов, ₽',
        'income_total' => 'Доход компании, ₽',
        'income_total_hint' => '≈ 20% от стоимости',
        'income_logist' => 'Доход логиста, ₽',
        'avg_check' => 'Средний чек, ₽',
        'avg_income' => 'Доход на рейс, ₽',
        'unfinished' => 'Незавершённых рейсов',
        'overdue' => 'Просроченных оплат',
        'overdue_hint' => 'срок истёк, оплата не внесена',
    ],

    'charts' => [
        'trips_by_day' => 'Рейсы по дням',
        'income_by_day' => 'Доход логиста по дням, ₽',
    ],

    'calendar' => [
        'title' => 'Календарь — подсветка дней с завершёнными/оплаченными рейсами',
    ],

    'weekdays' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],

    'day_trips' => ':day: :count рейс.',
    'selected_day' => 'Рейсы за',

    'table' => [
        'date' => 'Дата',
        'route' => 'Маршрут',
        'customer' => 'Заказчик',
        'driver' => 'Водитель',
        'cost' => 'Стоимость',
        'status' => 'Статус',
        'logistic' => 'Логист',
        'trips' => 'Рейсов',
        'income' => 'Доход, ₽',
    ],

    'top_customers' => 'Топ заказчиков за период',
    'latest_trips' => 'Последние рейсы',
];
