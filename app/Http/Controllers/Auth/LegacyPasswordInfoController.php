<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegacyPasswordInfoController extends Controller
{
    public function __invoke(): View
    {
        return view('auth.legacy-password-info', [
            'title' => config('legacy-auth.info_title'),
            'lead' => config('legacy-auth.info_lead'),
            'steps' => config('legacy-auth.info_steps'),
            'contactHint' => config('legacy-auth.info_contact_hint'),
            'accountEmail' => session('legacy_password_email'),
        ]);
    }
}
