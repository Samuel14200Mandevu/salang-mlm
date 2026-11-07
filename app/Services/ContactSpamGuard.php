<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContactSpamGuard
{
    public const SUBJECTS = [
        'Adhésion / inscription',
        'Produits & commandes',
        'Plan de rémunération',
        'Compte membre / connexion',
        'Partenariat',
        'Autre',
    ];

    /** @var list<string> */
    private const BLOCKED_EMAIL_DOMAINS = [
        'amazingleadgen.com',
        'ultraleadgeneration.com',
        'mailinator.com',
        'guerrillamail.com',
        'tempmail.com',
        '10minutemail.com',
        'yopmail.com',
        'trashmail.com',
        'sharklasers.com',
        'discard.email',
    ];

    /** @var list<string> */
    private const SPAM_PHRASES = [
        'seo plan',
        'seo services',
        'google rankings',
        'top google',
        'boost your website',
        'target keywords',
        'lead generation',
        'ultraleadgeneration',
        'web form',
        'ai voice agent',
        'not interested? unsubscribe',
        'customized seo',
        'grow your website traffic',
        'measurable improvements',
        'full proposal',
    ];

    /**
     * @return array{blocked: bool, reason: string|null}
     */
    public function inspect(Request $request, array $data): array
    {
        if ($this->honeypotFilled($request)) {
            return $this->block('honeypot');
        }

        if (! $this->timingOk($request)) {
            return $this->block('timing');
        }

        if ($this->blockedEmailDomain((string) ($data['email'] ?? ''))) {
            return $this->block('email_domain');
        }

        if (! in_array((string) ($data['subject'] ?? ''), self::SUBJECTS, true)) {
            return $this->block('subject');
        }

        if ($this->messageLooksLikeSpam((string) ($data['message'] ?? ''), (string) ($data['name'] ?? ''))) {
            return $this->block('content');
        }

        if ($this->isDuplicate($request, $data)) {
            return $this->block('duplicate');
        }

        return ['blocked' => false, 'reason' => null];
    }

    public function rememberSubmission(Request $request, array $data): void
    {
        Cache::put($this->duplicateKey($request, $data), 1, now()->addDay());
    }

    public function issueFormToken(): string
    {
        return Crypt::encryptString(json_encode([
            't' => now()->timestamp,
            'n' => Str::random(8),
        ]));
    }

    private function honeypotFilled(Request $request): bool
    {
        $website = trim((string) $request->input('website', ''));
        $company = trim((string) $request->input('company_url', ''));

        return $website !== '' || $company !== '';
    }

    private function timingOk(Request $request): bool
    {
        $token = (string) $request->input('form_token', '');
        if ($token === '') {
            return false;
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return false;
        }

        $started = (int) ($payload['t'] ?? 0);
        if ($started <= 0) {
            return false;
        }

        $elapsed = now()->timestamp - $started;

        // Trop rapide (< 3 s) = bot ; trop vieux (> 2 h) = token rejoué.
        return $elapsed >= 3 && $elapsed <= 7200;
    }

    private function blockedEmailDomain(string $email): bool
    {
        $email = Str::lower(trim($email));
        $domain = Str::after($email, '@');
        if ($domain === '' || $domain === $email) {
            return true;
        }

        foreach (self::BLOCKED_EMAIL_DOMAINS as $blocked) {
            if ($domain === $blocked || Str::endsWith($domain, '.'.$blocked)) {
                return true;
            }
        }

        return false;
    }

    private function messageLooksLikeSpam(string $message, string $name): bool
    {
        $haystack = Str::lower($message.' '.$name);

        foreach (self::SPAM_PHRASES as $phrase) {
            if (Str::contains($haystack, $phrase)) {
                return true;
            }
        }

        $linkCount = preg_match_all('/https?:\/\//i', $message) ?: 0;
        if ($linkCount >= 2) {
            return true;
        }

        // Message très court + lien externe
        if ($linkCount >= 1 && mb_strlen(trim($message)) < 80) {
            return true;
        }

        return false;
    }

    private function isDuplicate(Request $request, array $data): bool
    {
        return Cache::has($this->duplicateKey($request, $data));
    }

    private function duplicateKey(Request $request, array $data): string
    {
        return 'contact:dup:'.sha1(implode('|', [
            Str::lower(trim((string) ($data['email'] ?? ''))),
            Str::lower(trim((string) ($data['message'] ?? ''))),
            $request->ip(),
        ]));
    }

    /**
     * @return array{blocked: bool, reason: string}
     */
    private function block(string $reason): array
    {
        Log::info('Contact spam bloqué', [
            'reason' => $reason,
            'ip' => request()->ip(),
        ]);

        return ['blocked' => true, 'reason' => $reason];
    }
}
