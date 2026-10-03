<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Commission;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function profile()
    {
        return $this->show();
    }

    public function show()
    {
        $user = Auth::user()->load(['rank', 'wallet']);

        return $this->success(new UserResource($user));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return $this->success(new UserResource($user->fresh()->load(['rank', 'wallet'])), 'Profile updated.');
    }

    public function stats()
    {
        $user = Auth::user();

        return $this->success([
            'pv_personnel' => (float) ($user->pv_balance ?? 0),
            'pv_cumul' => (float) ($user->team_pv ?? 0),
            'monthly_pv' => (float) ($user->monthly_pv ?? 0),
            'rank' => $user->rank,
            'rank_level' => (int) ($user->rank_level ?? 1),
            'total_commission_paid' => (float) Commission::where('user_id', $user->id)->where('status', 'paid')->sum('amount'),
            'total_withdrawn' => (float) Withdrawal::where('user_id', $user->id)->where('status', 'completed')->sum('amount'),
            'direct_downlines' => User::where('parrain_id', $user->id)->where('is_active', true)->count(),
        ]);
    }

    public function updateAvatar(Request $request)
    {
        return $this->error('Avatar upload via API is not implemented yet.', 501);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect.', 422, [
                'current_password' => ['Invalid password.'],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return $this->success(null, 'Password updated.');
    }
}
