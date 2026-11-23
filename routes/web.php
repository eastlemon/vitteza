<?php

use App\Http\Controllers\CredentialCardController;
use Illuminate\Support\Facades\Route;

// Корень сайта сразу ведёт в панель: неавторизованный попадёт на форму
// входа Filament (/admin/login), авторизованный — на дашборд.
Route::redirect('/', '/admin', 301);

// Одноразовая карточка учётных данных сотрудника (данные из session flash).
Route::get('/credential-card', [CredentialCardController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('credential.card');
