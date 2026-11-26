<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Shows the employee credential card (system and mail login/password,
 * IMAP/SMTP settings). Data is passed via session flash from the
 * "Employees" resource at creation/password-reset time — plaintext passwords are never stored.
 */
class CredentialCardController extends Controller
{
    public function show(Request $request)
    {
        $creds = $request->session()->get('vitteza_creds');

        if (! $creds) {
            abort(404, __('credential.flash_missing'));
        }

        // one-time — delete after showing
        $request->session()->forget('vitteza_creds');

        return view('credential-card', $creds);
    }
}
