<?php

namespace App\Http\Controllers;

use App\Models\MlmSetting;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function about(): View
    {
        return view('site.about');
    }

    public function offer(): View
    {
        return view('site.offer');
    }

    public function remuneration(): View
    {
        return view('site.remuneration');
    }

    public function contact(): View
    {
        $site = config('salang-site');

        return view('site.contact', [
            'supportEmail' => MlmSetting::getValue('support_email', $site['email'] ?? 'support@salanggroup.com'),
            'supportPhone' => MlmSetting::getValue('support_phone', $site['phone'] ?? ''),
        ]);
    }
}
