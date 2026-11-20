<?php

use App\Http\Controllers\CredentialCardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Одноразовая карточка учётных данных сотрудника (данные из session flash).
Route::get('/credential-card', [CredentialCardController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('credential.card');
