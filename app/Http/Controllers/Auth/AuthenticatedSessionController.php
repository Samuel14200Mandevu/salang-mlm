<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\LoginIdentifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $loggedIn = $request->authenticate();

        if (! $loggedIn) {
            return redirect()->route('login.choose-account');
        }

        return $this->redirectAfterLogin($request);
    }

    public function chooseAccount(): View|RedirectResponse
    {
        $users = $this->pendingUsers();

        if ($users->isEmpty()) {
            return redirect()
                ->route('login')
                ->withErrors(['credentials' => 'La sélection de compte a expiré. Reconnectez-vous.']);
        }

        return view('auth.choose-account', [
            'accounts' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'sponsor_id' => $user->sponsor_id ?: '—',
                'email_masked' => LoginIdentifier::maskEmail($user->email),
            ]),
        ]);
    }

    public function storeChosenAccount(Request $request): RedirectResponse
    {
        $pendingIds = session(LoginRequest::PENDING_USER_IDS, []);
        $expiresAt = (int) session(LoginRequest::PENDING_EXPIRES_AT, 0);
        $remember = (bool) session(LoginRequest::PENDING_REMEMBER, false);

        if ($pendingIds === [] || $expiresAt < now()->getTimestamp()) {
            session()->forget([
                LoginRequest::PENDING_USER_IDS,
                LoginRequest::PENDING_REMEMBER,
                LoginRequest::PENDING_EXPIRES_AT,
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['credentials' => 'La sélection de compte a expiré. Reconnectez-vous.']);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'in:'.implode(',', array_map('intval', $pendingIds))],
        ], [
            'user_id.required' => 'Choisissez un compte.',
            'user_id.in' => 'Compte invalide.',
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);

        session()->forget([
            LoginRequest::PENDING_USER_IDS,
            LoginRequest::PENDING_REMEMBER,
            LoginRequest::PENDING_EXPIRES_AT,
        ]);

        Auth::login($user, $remember);

        return $this->redirectAfterLogin($request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->forget('error');
        $request->session()->forget('warning');

        return redirect()->route('login');
    }

    private function redirectAfterLogin(Request $request): RedirectResponse
    {
        $request->session()->regenerate();

        $user = Auth::user();

        $request->session()->forget('error');

        if (! $user->is_active) {
            return redirect()->route('activate.index')
                ->with('warning', 'Votre compte est inactif. Veuillez l\'activer pour recevoir des commissions.');
        }

        return redirect()->intended($user->loginRedirectUrl());
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function pendingUsers()
    {
        $pendingIds = session(LoginRequest::PENDING_USER_IDS, []);
        $expiresAt = (int) session(LoginRequest::PENDING_EXPIRES_AT, 0);

        if ($pendingIds === [] || $expiresAt < now()->getTimestamp()) {
            session()->forget([
                LoginRequest::PENDING_USER_IDS,
                LoginRequest::PENDING_REMEMBER,
                LoginRequest::PENDING_EXPIRES_AT,
            ]);

            return collect();
        }

        return User::query()
            ->whereIn('id', $pendingIds)
            ->orderBy('name')
            ->get();
    }
}
