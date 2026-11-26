<?php

declare(strict_types=1);

// Раздел «Рейсы» (TripResource, TripStatus, тесты).

return [
    'nav' => 'Рейсы',
    'model' => 'Рейс',
    'models' => 'Рейсы',

    'section' => [
        'trip' => 'Рейс',
        'cargo' => 'Груз',
        'docs' => 'Документы и оплата',
        'status' => 'Статус и доход',
    ],

    'fields' => [
        'load_date' => 'Дата погрузки',
        'unload_date' => 'Дата выгрузки',
        'cargo_template' => 'Шаблон груза (подставить параметры)',
        'act_no' => 'Номер акта',
        'sent_electronic_at' => 'Отправлено электронно',
        'sent_post_at' => 'Отправлено по почте',
        'delivered' => 'Вручено заказчику',
        'payment_due_date' => 'Срок оплаты',
        'payment_date' => 'Дата оплаты',
        'payment_fact' => 'Оплата факт, ₽',
        'paid_to_logist' => 'Доход начислен',
    ],

    'placeholder' => [
        'route' => 'Москва — Санкт-Петербург',
    ],

    'helpers' => [
        'cargo_template' => 'При выборе — параметры копируются в экземпляр груза рейса (§14.5).',
        'status' => 'При переходе в статус «Оплачен» доход начисляется в дашборд логиста (§10.5).',
    ],

    'filters' => [
        'load_period' => 'Период погрузки',
    ],

    'cols' => [
        'load_date' => 'Погрузка',
        'unload_date' => 'Выгрузка',
    ],

    'status' => [
        'new' => 'Новый',
        'confirmed' => 'Подтверждён',
        'in_progress' => 'В пути',
        'completed' => 'Выполнен',
        'docs_sent' => 'Документы отправлены',
        'await_pay' => 'Ожидается оплата',
        'paid' => 'Оплачен',
        'cancelled' => 'Отменён',
    ],
];
