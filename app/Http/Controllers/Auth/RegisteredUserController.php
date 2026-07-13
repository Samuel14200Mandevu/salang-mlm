<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'string'],
            'terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'This email address is already used by another account.',
            'sponsor_id.required' => 'Sponsor ID is required.',
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions.',
        ]);

        try {
            Log::info('Registration attempt', [
                'email' => $request->email,
                'sponsor_id_input' => $request->sponsor_id,
            ]);

            $sponsor = $this->findSponsor($request->sponsor_id);

            Log::info('Sponsor search result', [
                'sponsor_id_input' => $request->sponsor_id,
                'sponsor_found' => $sponsor ? 'YES' : 'NO',
                'sponsor_id' => $sponsor ? $sponsor->id : 'N/A',
                'sponsor_name' => $sponsor ? $sponsor->name : 'N/A',
            ]);

            if (!$sponsor) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'sponsor_id' => 'Invalid sponsor ID. Please check the code.'
                    ]);
            }

            if (User::where('email', $request->email)->exists()) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => 'This email address is already used by another account.'
                    ]);
            }

            DB::beginTransaction();

            $sponsorCode = $this->generateSponsorId();

            $rankId = Rank::where('slug', 'distributor')->first()?->id ?? 1;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'rank_id' => $rankId,
                'rank' => 'Distributor',
                'is_active' => true,
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_sponsors' => 0,
                'total_team' => 0,
                'qualified_branches' => 0,
                'direct_sponsors_count' => 0,
                'commission_balance' => 0,
                'total_earnings' => 0,
                'kyc_status' => 'not_submitted',
            ]);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'sponsor_id' => $user->sponsor_id,
                'parrain_id' => $user->parrain_id,
                'sponsor_name' => $sponsor->name,
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            $level = ($sponsor->genealogy?->level ?? 0) + 1;

            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'parent_id' => $sponsor->id,
                'level' => $level,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            $this->updateTeamCountersOptimized($sponsor, 1);

            DB::commit();

            try {
                $user->notify(new WelcomeNotification($sponsor->name));
            } catch (\Exception $e) {
                Log::error('Error sending welcome notification: ' . $e->getMessage());
            }

            event(new Registered($user));
            Auth::login($user);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome ' . $user->name . '! You are now a member of Salang Group.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('SQL error during registration: ' . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            if ($e->getCode() == 23000) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => 'This email address is already used by another account.'
                    ]);
            }

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'An error occurred during registration. Please try again.'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'An error occurred. Please try again later.'
                ]);
        }
    }

    private function findSponsor(string $sponsorId): ?User
    {
        if (is_numeric($sponsorId)) {
            $sponsor = User::find((int)$sponsorId);
            if ($sponsor) {
                Log::info('Sponsor found by user ID', ['id' => $sponsorId, 'name' => $sponsor->name]);
                return $sponsor;
            }
        }

        $sponsor = User::where('sponsor_id', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor found by sponsor_id', ['sponsor_id' => $sponsorId, 'name' => $sponsor->name]);
            return $sponsor;
        }

        $sponsor = User::where('email', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor found by email', ['email' => $sponsorId, 'name' => $sponsor->name]);
            return $sponsor;
        }

        $sponsor = User::where('name', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor found by name', ['name' => $sponsorId, 'id' => $sponsor->id]);
            return $sponsor;
        }

        Log::warning('Sponsor NOT found', ['sponsor_id' => $sponsorId]);
        return null;
    }

    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $random = strtoupper(substr(uniqid(), -6));
            $sponsorCode = $prefix . $random;
            $attempts++;
        } while (User::where('sponsor_id', $sponsorCode)->exists() && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            $sponsorCode = $prefix . strtoupper(substr(md5(time() . rand()), -6));
        }

        return $sponsorCode;
    }

    private function updateTeamCountersOptimized(User $user, int $level): void
    {
        if ($level > 5) {
            return;
        }

        $currentUser = $user;
        $currentLevel = $level;

        while ($currentUser->parrain_id && $currentLevel <= 5) {
            try {
                $sponsor = User::find($currentUser->parrain_id);

                if (!$sponsor) {
                    break;
                }

                $sponsor->increment('total_team');
                $sponsor->save();

                $currentUser = $sponsor;
                $currentLevel++;

            } catch (\Exception $e) {
                Log::warning('Error updating team counters', [
                    'user_id' => $currentUser->id,
                    'level' => $currentLevel,
                    'error' => $e->getMessage()
                ]);
                break;
            }
        }
    }
}