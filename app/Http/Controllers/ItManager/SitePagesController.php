<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\MlmSetting;
use App\Services\SiteContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePagesController extends Controller
{
    public function __construct(
        protected SiteContentService $content
    ) {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function edit(): View
    {
        return view('it-manager.site-pages.edit', [
            'supportEmail' => $this->content->supportEmail(),
            'supportPhone' => $this->content->supportPhone(),
            'aboutMission' => $this->content->aboutMission(),
            'aboutWhoWeAre' => $this->content->aboutWhoWeAre(),
            'offerLead' => $this->content->offerLead(),
            'membershipFee' => $this->content->membershipFeeUsd(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'support_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:40'],
            'about_mission' => ['required', 'string', 'max:2000'],
            'about_who_we_are' => ['required', 'string', 'max:4000'],
            'offer_lead' => ['required', 'string', 'max:1000'],
            'membership_fee_usd' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        MlmSetting::setValue('support_email', $data['support_email'], 'contact', 'Email support site');
        MlmSetting::setValue('support_phone', $data['support_phone'] ?? '', 'contact', 'Téléphone support site');
        MlmSetting::setValue('site_about_mission', $data['about_mission'], 'site', 'Mission page À propos');
        MlmSetting::setValue('site_about_who', $data['about_who_we_are'], 'site', 'Texte Qui sommes-nous');
        MlmSetting::setValue('site_offer_lead', $data['offer_lead'], 'site', 'Accroche page Offre');
        MlmSetting::setValue('site_membership_fee_usd', (string) $data['membership_fee_usd'], 'site', 'Frais adhésion USD');

        return back()->with('success', 'Pages site mises à jour.');
    }
}
