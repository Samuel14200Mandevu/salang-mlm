<?php

namespace App\Http\Controllers;

use App\Services\ContactSpamGuard;
use App\Services\SiteContentService;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function __construct(
        protected SiteContentService $content,
        protected ContactSpamGuard $contactSpamGuard
    ) {
    }

    public function about(): View
    {
        return view('site.about', [
            'aboutMission' => $this->content->aboutMission(),
            'aboutWhoWeAre' => $this->content->aboutWhoWeAre(),
        ]);
    }

    public function offer(): View
    {
        return view('site.offer', [
            'offerLead' => $this->content->offerLead(),
            'membershipFeeUsd' => $this->content->membershipFeeUsd(),
        ]);
    }

    public function remuneration(): View
    {
        return view('site.remuneration');
    }

    public function contact(): View
    {
        return view('site.contact', [
            'supportEmail' => $this->content->supportEmail(),
            'supportPhone' => $this->content->supportPhone(),
            'formToken' => $this->contactSpamGuard->issueFormToken(),
            'subjectOptions' => ContactSpamGuard::SUBJECTS,
        ]);
    }
}
