<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\LoginIdentifier;
use App\Support\UserPassword;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const PENDING_USER_IDS = 'login.pending_user_ids';

    public const PENDING_REMEMBER = 'login.pending_remember';

    public const PENDING_EXPIRES_AT = 'login.pending_expires_at';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Case à cocher HTML : valeur "on" ou "1", pas un booléen JSON.
        // Compat : anciens clients / bookmarks qui postent encore "email".
        $login = $this->input('login', $this->input('email'));

        $this->merge([
            'login' => is_string($login) ? trim($login) : $login,
            'remember' => $this->boolean('remember'),
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Indiquez votre email, votre ID membre ou votre téléphone.',
            'login.max' => 'L’identifiant ne doit pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    /**
     * Authentifie l'utilisateur, ou prépare le choix de compte si plusieurs matchent.
     *
     * @return bool true si connecté, false s'il faut choisir un compte
     *
     * @throws ValidationException
     */
    public function authenticate(): bool
    {
        $this->ensureIsNotRateLimited();

        $candidates = LoginIdentifier::findUsers((string) $this->input('login'));

        if ($candidates->isEmpty()) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }

        $password = $this->string('password');

        $legacyOnly = $candidates->filter(fn (User $user) => UserPassword::usesLegacyMd5($user));
        $modern = $candidates->reject(fn (User $user) => UserPassword::usesLegacyMd5($user));

        $valid = $modern
            ->filter(fn (User $user) => UserPassword::verify($user, $password))
            ->values();

        if ($valid->isEmpty()) {
            if ($legacyOnly->isNotEmpty() && $modern->isEmpty()) {
                RateLimiter::clear($this->throttleKey());
                session()->flash('legacy_password_email', $legacyOnly->first()->email);

                throw ValidationException::withMessages([
                    'legacy' => config('legacy-auth.login_error'),
                ]);
            }

            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        if ($valid->count() === 1) {
            Auth::login($valid->first(), $this->boolean('remember'));
            $this->clearPendingAccountChoice();

            return true;
        }

        session([
            self::PENDING_USER_IDS => $valid->pluck('id')->map(fn ($id) => (int) $id)->all(),
            self::PENDING_REMEMBER => $this->boolean('remember'),
            self::PENDING_EXPIRES_AT => now()->addMinutes(10)->getTimestamp(),
        ]);

        return false;
    }

    public function clearPendingAccountChoice(): void
    {
        session()->forget([
            self::PENDING_USER_IDS,
            self::PENDING_REMEMBER,
            self::PENDING_EXPIRES_AT,
        ]);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'throttle' => 'Trop de tentatives de connexion. Réessayez dans '.max(1, (int) ceil($seconds / 60)).' minute(s).',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
