<?php

namespace App\Services;

use App\Models\MlmSetting;

class SiteContentService
{
    public function supportEmail(): string
    {
        return (string) MlmSetting::getValue(
            'support_email',
            config('salang-site.email', 'support@salanggroup.com')
        );
    }

    public function supportPhone(): string
    {
        return (string) MlmSetting::getValue(
            'support_phone',
            config('salang-site.phone', '')
        );
    }

    public function aboutMission(): string
    {
        return (string) MlmSetting::getValue(
            'site_about_mission',
            config('salang-site.mission', '')
        );
    }

    public function aboutWhoWeAre(): string
    {
        return (string) MlmSetting::getValue(
            'site_about_who',
            config('salang-site.who_we_are', '')
        );
    }

    public function offerLead(): string
    {
        $default = 'Produits naturels, séances Ceragem & Détox, et un plan de carrière clair.';

        return (string) MlmSetting::getValue('site_offer_lead', $default);
    }

    public function membershipFeeUsd(): int
    {
        return (int) MlmSetting::getValue(
            'site_membership_fee_usd',
            config('salang-site.membership_fee_usd', 30)
        );
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function faqs(): array
    {
        $faqs = MlmSetting::getArray('member_faqs', []);

        return array_values(array_filter($faqs, function ($row) {
            return is_array($row)
                && filled($row['question'] ?? null)
                && filled($row['answer'] ?? null);
        }));
    }

    /**
     * @return list<array{id: string, title: string, answer: string, keywords: list<string>, route: string|null, route_label: string|null}>
     */
    public function faqArticlesForAssistant(): array
    {
        $out = [];
        foreach ($this->faqs() as $i => $faq) {
            $out[] = [
                'id' => 'faq-'.$i,
                'title' => $faq['question'],
                'answer' => $faq['answer'],
                'keywords' => preg_split('/\s+/u', mb_strtolower($faq['question'])) ?: [],
                'route' => 'profile.help',
                'route_label' => 'Centre d’aide',
            ];
        }

        return $out;
    }
}
