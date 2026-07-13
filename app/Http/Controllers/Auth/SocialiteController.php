<?php
// app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    protected $providers = ['google', 'facebook', 'twitter', 'instagram', 'tiktok'];

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'This provider is not supported.');
        }

        $sponsorId = session('sponsor_id') ?? request()->query('ref') ?? request()->input('sponsor_id');

        if (!$sponsorId) {
            return redirect('/register')->with('error', 'You must have a sponsor ID to register.');
        }

        $sponsor = User::find($sponsorId) ?? User::where('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            return redirect('/register')->with('error', 'Invalid sponsor ID. Please try again.');
        }

        session(['sponsor_id' => $sponsor->id]);
        session(['social_provider' => $provider]);

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Socialite redirect error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Connection error with ' . ucfirst($provider) . '. Please try again.');
        }
    }

    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'This provider is not supported.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Authentication error with ' . ucfirst($provider) . '. Please try again.');
        }

        if (!$socialUser->getEmail()) {
            return redirect('/register')->with('error', 'No email address found with this account.');
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $providerColumn = $provider . '_id';
            if (empty($user->$providerColumn)) {
                $user->$providerColumn = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->last_provider = $provider;
                $user->save();
            }

            session()->forget('sponsor_id');
            session()->forget('social_provider');
            Auth::login($user);

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back ' . $user->name . '!');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Welcome back ' . $user->name . '!');
        }

        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/register')->with('error', 'Sponsor ID required for registration.');
        }

        $sponsor = User::find($sponsorId);
        if (!$sponsor) {
            session()->forget('sponsor_id');
            session()->forget('social_provider');
            return redirect('/register')->with('error', 'Invalid sponsor ID.');
        }

        $sponsorCode = $this->generateSponsorId();

        try {
            $rankId = Rank::where('slug', 'distributor')->first()?->id ?? 1;

            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'avatar' => $socialUser->getAvatar(),
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

            $providerColumn = $provider . '_id';
            $user->$providerColumn = $socialUser->getId();
            $user->last_provider = $provider;
            $user->save();

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'parent_id' => $sponsor->id,
                'level' => ($sponsor->genealogy?->level ?? 0) + 1,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            session()->forget('sponsor_id');
            session()->forget('social_provider');

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome ' . $user->name . '! Your account has been created with ' . ucfirst($provider) . '.');

        } catch (\Exception $e) {
            Log::error('Social registration error: ' . $e->getMessage());
            return redirect('/register')->with('error', 'Error creating account. Please try again.');
        }
    }

    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $random = strtoupper(Str::random(6));
        $sponsorCode = $prefix . $random;

        while (User::where('sponsor_id', $sponsorCode)->exists()) {
            $random = strtoupper(Str::random(6));
            $sponsorCode = $prefix . $random;
        }

        return $sponsorCode;
    }

    public function storeSponsor(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ], [
            'sponsor_id.required' => 'Sponsor ID is required.',
        ]);

        $sponsor = User::find($request->sponsor_id) ?? User::where('sponsor_id', $request->sponsor_id)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sponsor ID. No user found.'
            ], 422);
        }

        session(['sponsor_id' => $sponsor->id]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor ID validated.',
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $sponsor->email,
        ]);
    }
}