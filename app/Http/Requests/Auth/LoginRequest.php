<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\UserPassword;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L’adresse email est obligatoire.',
            'email.email' => 'Saisissez une adresse email valide (exemple : nom@domaine.com).',
            'email.max' => 'L’adresse email ne doit pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }

        if (UserPassword::usesLegacyMd5($user)) {
            RateLimiter::clear($this->throttleKey());
            session()->flash('legacy_password_email', $user->email);

            throw ValidationException::withMessages([
                'legacy' => config('legacy-auth.login_error'),
            ]);
        }

        if (!UserPassword::verify($user, $this->string('password'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
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
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
