<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Показывает карточку учётных данных сотрудника (логин/пароль системы и почты,
 * настройки IMAP/SMTP). Данные передаются через session flash из ресурса
 * «Сотрудники» в момент создания/сброса пароля — plaintext-пароли нигде не хранятся.
 */
class CredentialCardController extends Controller
{
    public function show(Request $request)
    {
        $creds = $request->session()->get('vitteza_creds');

        if (! $creds) {
            abort(404, 'Карточка недоступна. Сгенерируйте её заново через карточку сотрудника.');
        }

        // одноразовая — удалить после показа
        $request->session()->forget('vitteza_creds');

        return view('credential-card', $creds);
    }
}
