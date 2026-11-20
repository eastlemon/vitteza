<?php

use App\Http\Controllers\CredentialCardController;
use Illuminate\Support\Facades\Route;

// Маршрут одноразовой карточки учётных данных сотрудника.
// Данные передаются через session flash из ресурса «Сотрудники».
Route::get('/credential-card', [CredentialCardController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('credential.card');
