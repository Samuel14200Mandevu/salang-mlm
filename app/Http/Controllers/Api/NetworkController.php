<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->stats();
    }

    public function stats()
    {
        $user = Auth::user();
        $direct = User::where('parrain_id', $user->id)->count();
        $activeDirect = User::where('parrain_id', $user->id)->where('is_active', true)->count();

        return $this->success([
            'total_direct' => $direct,
            'active_direct' => $activeDirect,
            'team_pv' => (float) ($user->team_pv ?? 0),
            'total_team' => (int) ($user->total_team ?? 0),
        ]);
    }

    public function tree()
    {
        $user = Auth::user();
        $children = User::where('parrain_id', $user->id)
            ->with(['rank', 'package'])
            ->limit(50)
            ->get();

        return $this->success([
            'user' => new UserResource($user),
            'children' => UserResource::collection($children),
        ]);
    }

    public function downlines()
    {
        $user = Auth::user();
        $downlines = User::where('parrain_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success([
            'items' => UserResource::collection($downlines->items()),
            'pagination' => [
                'current_page' => $downlines->currentPage(),
                'last_page' => $downlines->lastPage(),
                'per_page' => $downlines->perPage(),
                'total' => $downlines->total(),
            ],
        ]);
    }

    public function search()
    {
        return $this->error('Network search API is not implemented yet.', 501);
    }
}
