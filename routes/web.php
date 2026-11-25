<?php

use App\Http\Controllers\CredentialCardController;
use Illuminate\Support\Facades\Route;

// The site root goes straight to the panel: an unauthenticated user lands on the
// Filament login form (/admin/login), an authenticated one — on the dashboard.
Route::redirect('/', '/admin', 301);

// One-time employee credential card (data from session flash).
Route::get('/credential-card', [CredentialCardController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('credential.card');
