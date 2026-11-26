<?php

declare(strict_types=1);

// Раздел «Сотрудники» (EmployeeResource и его страницы).

return [
    'nav' => 'Сотрудники',
    'model' => 'Сотрудник',
    'models' => 'Сотрудники',

    'section' => [
        'main' => 'Основное',
        'access' => 'Доступ и почта',
    ],

    'fields' => [
        'login_hint' => 'Логин для входа (система и почта)',
        'competences' => 'Компетенции (для роли «Пользователь»)',
        'mail_quota' => 'Квота почтового ящика, MB',
        'create_mailbox' => 'Создать почтовый ящик @vitteza.ru',
    ],

    'roles' => [
        'admin' => 'Администратор',
        'user' => 'Пользователь',
    ],

    'comps' => [
        'trips_view' => 'Рейсы: просмотр',
        'trips_view_all' => 'Рейсы: видеть все (а не только свои)',
        'trips_manage' => 'Рейсы: управление',
        'drivers_view' => 'Водители: просмотр',
        'drivers_view_sensitive' => 'Водители: чувствительные поля',
        'drivers_manage' => 'Водители: управление',
        'customers_view' => 'Заказчики: просмотр',
        'customers_manage' => 'Заказчики: управление',
        'cargo_view' => 'Грузы: просмотр',
        'cargo_manage' => 'Грузы: управление',
        'logists_view' => 'Логисты: просмотр',
        'finance_view' => 'Финансы: просмотр',
    ],

    'helpers' => [
        'one_password' => 'Один пароль для входа в систему и для почты.',
    ],

    'fill_login_first' => '— заполните логин —',

    'cols' => [
        'blocked' => 'Блокировка',
    ],
];
