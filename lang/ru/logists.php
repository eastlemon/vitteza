<?php

declare(strict_types=1);

// Виртуальная вкладка «Логисты» (LogistsPage + blade).

return [
    'nav' => 'Логисты',
    'title' => 'Логисты',

    'cols' => [
        'name' => 'ФИО логиста',
        'trips' => 'Рейсов',
        'load_range' => 'Погрузка (первая/последняя)',
    ],

    'active' => 'активен',
    'blocked' => 'блок',
];
