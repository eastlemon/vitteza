<?php

declare(strict_types=1);

// Раздел «Грузы» (CargoTemplateResource — шаблоны грузов).

return [
    'nav' => 'Грузы',
    'model' => 'Шаблон груза',
    'models' => 'Грузы (шаблоны)',

    'section' => [
        'main' => 'Шаблон груза',
    ],

    'fields' => [
        'name' => 'Наименование груза',
        'cargo_class' => 'Класс груза',
        'description' => 'Описание',
        'default_weight_t' => 'Типовой вес, т',
        'default_volume_m3' => 'Типовой объём, м³',
        'default_packaging' => 'Типовая упаковка',
        'default_pieces' => 'Типовое кол-во мест',
        'default_dimensions' => 'Габариты Д×Ш×В, м',
        'temperature_regime' => 'Температурный режим',
        'special_conditions' => 'Особые условия перевозки',
        'declared_value' => 'Объявленная ценность, ₽',
    ],

    'classes' => [
        'general' => 'Обычный',
        'dangerous' => 'Опасный',
        'perishable' => 'Скоропортящийся',
        'oversized' => 'Негабаритный',
        'liquid' => 'Наливной',
        'bulk' => 'Насыпной',
    ],

    'cols' => [
        'name' => 'Груз',
        'cargo_class' => 'Класс',
    ],
];
