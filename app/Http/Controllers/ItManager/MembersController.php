<?php

namespace App\Http\Controllers\ItManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active', 'role:it_manager|admin']);
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $members = User::query()
            ->select(['id', 'name', 'sponsor_id', 'phone', 'email', 'is_active', 'user_type', 'created_at'])
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('sponsor_id', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->where(function ($query) {
                $query->whereNull('user_type')
                    ->orWhere('user_type', '!=', 'client')
                    ->orWhereNotNull('sponsor_id');
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('it-manager.members.index', compact('members', 'q'));
    }
}
