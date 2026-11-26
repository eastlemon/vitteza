<?php

declare(strict_types=1);

// Раздел «Заказчики» (CustomerResource, договоры, реквизиты).

return [
    'nav' => 'Заказчики',
    'model' => 'Заказчик',
    'models' => 'Заказчики',

    'section' => [
        'main' => 'Заказчик',
        'requisites' => 'Реквизиты для документов (§13.3)',
        'contracts' => 'Договоры (§13.4)',
    ],

    'fields' => [
        'name' => 'Наименование организации',
        'full_legal_name' => 'Полное юр. наименование',
        'inn' => 'ИНН',
        'kpp' => 'КПП',
        'ogrn' => 'ОГРН',
        'contact_person' => 'Контактное лицо',
        'legal_address' => 'Юридический адрес',
        'postal_address' => 'Почтовый адрес для документов',
        'account_number' => 'Расчётный счёт',
        'bank_name' => 'Банк',
        'bank_bik' => 'БИК',
        'correspondent_account' => 'Корр. счёт',
        'signed_at' => 'Дата заключения',
        'expires_at' => 'Срок действия',
        'subject' => 'Предмет договора',
        'contract_active' => 'Действует',
    ],

    'cols' => [
        'name' => 'Организация',
        'contact' => 'Контакт',
    ],
];
