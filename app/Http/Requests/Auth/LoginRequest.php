<?php
// app/Http/Requests/Auth/LoginRequest.php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'email.required' => '📧 L\'adresse email est obligatoire.',
            'email.email' => '📧 Veuillez saisir une adresse email valide (exemple: nom@domaine.com).',
            'email.max' => '📧 L\'adresse email ne doit pas dépasser 255 caractères.',
            'password.required' => '🔑 Le mot de passe est obligatoire.',
            'password.min' => '🔑 Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }

    /**
     * Authentifier l'utilisateur
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // ✅ Vérifier si l'utilisateur existe
        $user = \App\Models\User::where('email', $this->email)->first();
        
        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => '❌ Aucun compte trouvé avec cette adresse email.',
            ]);
        }

        // ✅ Vérifier si le compte est actif
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => '⛔ Votre compte a été désactivé. Veuillez contacter le support.',
            ]);
        }

        // ✅ Tentative de connexion
        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'password' => '🔑 Le mot de passe saisi est incorrect.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Vérifier le rate limiting
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => '⏳ Trop de tentatives de connexion. Veuillez réessayer dans ' . ceil($seconds / 60) . ' minute(s).',
        ]);
    }

    /**
     * Clé de rate limiting
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}