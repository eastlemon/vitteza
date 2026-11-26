<?php

declare(strict_types=1);

// Страница «Настройки» (SettingsPage + blade) — финансовая формула и веса рейтинга.

return [
    'nav' => 'Настройки',
    'saved' => 'Настройки сохранены',

    'finance_title' => 'Финансовая формула (§15.4)',
    'finance_formula' => 'Доход компании = стоимость рейса × доля дохода. Доход логиста = доход компании × доля логиста.',
    'income_share' => 'Доля дохода (от стоимости)',
    'logist_share' => 'Доля логиста (от дохода)',
    'trigger' => 'Статус-триггер дохода',

    'weights_title' => 'Веса рейтинга логистов (§16.2)',
    'weights_formula' => 'Балл = W_рейсов × (рейсы/max) + W_дохода × (доход/max). Сумма весов обычно = 1.',
    'w_trips' => 'Вес числа рейсов',
    'w_income' => 'Вес дохода',

    'hint_income_share' => 'income_share (по умолч. 0,20)',
    'hint_logist_share' => 'logist_share (по умолч. 0,50)',
    'hint_trigger' => 'income_trigger_status (по умолч. paid)',
    'hint_w_trips' => 'rating_w_trips (по умолч. 0,40)',
    'hint_w_income' => 'rating_w_income (по умолч. 0,60)',
];
