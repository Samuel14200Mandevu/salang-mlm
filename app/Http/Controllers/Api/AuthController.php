<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Genealogy;
use App\Models\Rank;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'string', 'regex:/^51[0-9]{4}$/'],
        ]);

        $sponsor = User::where('sponsor_id', $validated['sponsor_id'])
            ->where('is_active', true)
            ->first();

        if (!$sponsor) {
            return $this->error('Invalid or inactive sponsor code.', 422, [
                'sponsor_id' => ['Invalid sponsor ID.'],
            ]);
        }

        $user = DB::transaction(function () use ($validated, $sponsor, $request) {
            $rankId = Rank::where('slug', 'distributor')->first()?->id ?? 1;
            $sponsorCode = $this->generateSponsorId();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'rank_id' => $rankId,
                'rank' => 'Distributor',
                'is_active' => false,
                'ip_address' => $request->ip(),
                'kyc_status' => 'not_submitted',
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
                'position' => 'left',
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            $sponsor->increment('total_sponsors');

            return $user;
        });

        return $this->success([
            'user' => new UserResource($user),
            'activation_required' => true,
        ], 'Account created. Activation required before login.', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (\App\Support\UserPassword::usesLegacyMd5($user)) {
            return $this->error(config('legacy-auth.login_error'), 403, [
                'legacy_password' => true,
                'info_url' => route('login.legacy-info'),
            ]);
        }

        if (!\App\Support\UserPassword::verify($user, $credentials['password'])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();

            return $this->error('Your account is inactive. Complete activation first.', 403);
        }

        $deviceName = $credentials['device_name'] ?? 'api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load(['rank', 'wallet'])),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Authenticated.');
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->success(null, 'Logged out.');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['rank', 'wallet']);

        return $this->success(new UserResource($user));
    }

    /** @deprecated Use me() — kept for legacy route /api/user */
    public function user(Request $request)
    {
        return $this->me($request);
    }

    public function sendResetLinkEmail(Request $request)
    {
        return $this->error('Password reset via API is not implemented yet.', 501);
    }

    public function resetPassword(Request $request)
    {
        return $this->error('Password reset via API is not implemented yet.', 501);
    }

    private function generateSponsorId(): string
    {
        do {
            $code = '51' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('sponsor_id', $code)->exists());

        return $code;
    }
}
