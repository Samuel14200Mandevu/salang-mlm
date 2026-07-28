<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rank;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Commission;
use App\Models\RankHistory;
use App\Notifications\WelcomeNotification;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index(Request $request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('rank')) {
            $query->where('rank_id', $request->rank);
        }

        if ($request->filled('package')) {
            $query->where('package_id', $request->package);
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count(),
            'cashiers' => User::whereHas('roles', function($q) {
                $q->where('name', 'cashier');
            })->count(),
            'with_package' => User::whereNotNull('package_id')->count(),
            'with_kyc' => User::where('kyc_status', 'verified')->count(),
            'pending_kyc' => User::where('kyc_status', 'pending')->count(),
        ];

        $ranks = Rank::orderBy('level')->get();
        $packages = Package::orderBy('price')->get();
        $kycStatuses = ['not_submitted', 'pending', 'partial', 'verified', 'rejected'];

        return view('admin.users.index', compact('users', 'stats', 'ranks', 'packages', 'kycStatuses'));
    }

    public function show($id)
    {
        $user = User::with(['rank', 'package', 'wallet'])->findOrFail($id);

        $parrain = User::find($user->parrain_id);

        $filleuls = User::where('parrain_id', $user->id)->get();
        $filleulsCount = $filleuls->count();
        $filleulsActifs = $filleuls->where('is_active', true)->count();

        $commissionsStats = [
            'total' => $user->commissions()->where('status', 'paid')->sum('amount'),
            'direct' => $user->commissions()->where('type', 'direct')->where('status', 'paid')->sum('amount'),
            'indirect' => $user->commissions()->where('type', 'indirect')->where('status', 'paid')->sum('amount'),
            'leadership' => $user->commissions()->where('type', 'leadership')->where('status', 'paid')->sum('amount'),
            'retail' => $user->commissions()->where('type', 'retail')->where('status', 'paid')->sum('amount'),
            'pending' => $user->commissions()->where('status', 'pending')->sum('amount'),
            'count' => $user->commissions()->where('status', 'paid')->count(),
        ];

        $rankProgress = $this->rankCalculator->getProgress($user);

        $rankHistory = $user->rankHistory()->with(['oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentTransactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentCommissions = $user->commissions()
            ->with(['fromUser', 'package', 'period'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $tree = $this->buildTree($user, 0, 3);

        $packages = Package::where('is_active', true)->get();

        return view('admin.users.show', compact(
            'user',
            'parrain',
            'filleuls',
            'filleulsCount',
            'filleulsActifs',
            'commissionsStats',
            'rankProgress',
            'rankHistory',
            'recentTransactions',
            'recentCommissions',
            'tree',
            'packages'
        ));
    }

    private function buildTree($user, $level, $maxLevel)
    {
        if ($level > $maxLevel) {
            return null;
        }

        $children = User::where('parrain_id', $user->id)->get();

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children->map(function($child) use ($level, $maxLevel) {
                return $this->buildTree($child, $level + 1, $maxLevel);
            })->filter()->values()->toArray(),
        ];
    }

    public function create()
    {
        $ranks = Rank::orderBy('level')->get();
        $packages = Package::orderBy('price')->get();
        $users = User::select('id', 'name', 'email', 'sponsor_id')
            ->whereNotNull('sponsor_id')
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('ranks', 'packages', 'users'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,cashier,admin',
            'is_active' => 'boolean',
        ];

        if ($request->role !== 'cashier') {
            $rules['package_id'] = 'nullable|exists:packages,id';
            $rules['parrain_id'] = 'nullable|exists:users,id';
            $rules['rank_id'] = 'nullable|exists:ranks,id';
            $rules['kyc_status'] = 'nullable|in:not_submitted,pending,partial,verified,rejected';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            $role = $request->role;

            if ($role === 'cashier') {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone ?? 'N/A',
                    'password' => Hash::make($request->password),
                    'address' => $request->address ?? null,
                    'city' => $request->city ?? null,
                    'country' => $request->country ?? null,
                    'is_active' => $request->has('is_active'),
                    'sponsor_id' => null,
                    'parrain_id' => null,
                    'rank_id' => null,
                    'rank' => 'Distributeur',
                    'rank_level' => 1,
                    'package_id' => null,
                    'pv_balance' => 0,
                    'bv_balance' => 0,
                    'monthly_pv' => 0,
                    'monthly_bv' => 0,
                    'team_pv' => 0,
                    'team_bv' => 0,
                    'total_team' => 0,
                    'total_sponsors' => 0,
                    'qualified_branches' => 0,
                    'direct_sponsors_count' => 0,
                    'commission_balance' => 0,
                    'total_earnings' => 0,
                    'kyc_status' => 'not_submitted',
                ]);

                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);

                $user->assignRole('cashier');

                DB::commit();

                Log::info('Nouveau caissier créé (sans code de parrain)', [
                    'admin_id' => auth()->id(),
                    'cashier_id' => $user->id,
                    'cashier_name' => $user->name,
                ]);

                return redirect()->route('admin.users')
                    ->with('success', 'Caissier créé avec succès !');

            } else {
                $parrain = null;
                if ($request->filled('parrain_id')) {
                    $parrain = User::find($request->parrain_id);
                }

                $sponsorCode = $this->generateSponsorCode();

                $rankId = $request->rank_id;
                if (!$rankId && $request->package_id) {
                    $package = Package::find($request->package_id);
                    if ($package) {
                        $rank = Rank::where('min_pv', '<=', $package->pv_value)
                            ->orderBy('level', 'desc')
                            ->first();
                        $rankId = $rank?->id;
                    }
                }

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone ?? 'N/A',
                    'password' => Hash::make($request->password),
                    'address' => $request->address ?? null,
                    'city' => $request->city ?? null,
                    'country' => $request->country ?? null,
                    'rank_id' => $rankId,
                    'rank' => $rankId ? Rank::find($rankId)?->name : 'Distributeur',
                    'rank_level' => $rankId ? Rank::find($rankId)?->level : 1,
                    'package_id' => $request->package_id,
                    'parrain_id' => $parrain?->id,
                    'sponsor_id' => $sponsorCode,
                    'is_active' => $request->has('is_active'),
                    'kyc_status' => $request->kyc_status ?? 'not_submitted',
                    'pv_balance' => $request->package_id ? Package::find($request->package_id)?->pv_value ?? 0 : 0,
                    'bv_balance' => $request->package_id ? Package::find($request->package_id)?->bv_value ?? 0 : 0,
                ]);

                if (!Wallet::where('user_id', $user->id)->exists()) {
                    Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'pending_balance' => 0,
                        'currency' => 'USD',
                        'is_active' => true,
                    ]);
                }

                Genealogy::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $parrain?->id,
                    'parent_id' => $parrain?->id,
                    'level' => $parrain ? ($parrain->genealogy?->level ?? 0) + 1 : 0,
                    'position' => null,
                    'left_count' => 0,
                    'right_count' => 0,
                    'total_children' => 0,
                ]);

                if ($role === 'admin') {
                    $user->assignRole('admin');
                } else {
                    $user->assignRole('user');
                }

                if ($parrain) {
                    $parrain->increment('total_sponsors');
                    $parrain->increment('total_team');
                    $this->updateTeamCounters($parrain);
                }

                DB::commit();

                try {
                    $user->notify(new WelcomeNotification($parrain?->name));
                } catch (\Exception $e) {
                    Log::error('Error sending welcome notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }

                return redirect()->route('admin.users')
                    ->with('success', "Utilisateur créé avec succès. Code de parrain: {$sponsorCode}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création utilisateur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::with(['rank', 'package'])->findOrFail($id);
        $ranks = Rank::orderBy('level')->get();
        $packages = Package::orderBy('price')->get();
        $users = User::select('id', 'name', 'email', 'sponsor_id')
            ->where('id', '!=', $id)
            ->whereNotNull('sponsor_id')
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'ranks', 'packages', 'users'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'role' => 'required|in:user,cashier,admin',
        ];

        if ($request->role !== 'cashier') {
            $rules['package_id'] = 'nullable|exists:packages,id';
            $rules['parrain_id'] = 'nullable|exists:users,id|not_in:' . $id;
            $rules['rank_id'] = 'nullable|exists:ranks,id';
            $rules['kyc_status'] = 'nullable|in:not_submitted,pending,partial,verified,rejected';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? 'N/A',
                'is_active' => $request->has('is_active'),
            ];

            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8|confirmed']);
                $data['password'] = Hash::make($request->password);
            }

            // Si le rôle est cashier, supprimer les données MLM mais garder 'rank' avec une valeur
            if ($request->role === 'cashier') {
                $data['sponsor_id'] = null;
                $data['parrain_id'] = null;
                $data['rank_id'] = null;
                $data['rank'] = 'Distributeur';
                $data['rank_level'] = 1;
                $data['package_id'] = null;
                $data['pv_balance'] = 0;
                $data['bv_balance'] = 0;
                $data['monthly_pv'] = 0;
                $data['monthly_bv'] = 0;
                $data['team_pv'] = 0;
                $data['team_bv'] = 0;
                $data['total_team'] = 0;
                $data['total_sponsors'] = 0;
                $data['qualified_branches'] = 0;
                $data['direct_sponsors_count'] = 0;
            } else {
                // Gérer le parrain
                if ($request->has('parrain_id') && $request->parrain_id != $user->parrain_id) {
                    if ($user->parrain_id) {
                        $oldParrain = User::find($user->parrain_id);
                        if ($oldParrain) {
                            $oldParrain->decrement('total_sponsors');
                            $this->updateTeamCountersDec($oldParrain);
                        }
                    }

                    if ($request->parrain_id) {
                        $newParrain = User::find($request->parrain_id);
                        if ($newParrain && $newParrain->id != $user->id) {
                            $newParrain->increment('total_sponsors');
                            $this->updateTeamCounters($newParrain);
                        }
                    }

                    $data['parrain_id'] = $request->parrain_id;

                    $genealogy = Genealogy::where('user_id', $user->id)->first();
                    if ($genealogy) {
                        $newParrain = $request->parrain_id ? User::find($request->parrain_id) : null;
                        $genealogy->sponsor_id = $newParrain?->id;
                        $genealogy->parent_id = $newParrain?->id;
                        $genealogy->level = $newParrain ? ($newParrain->genealogy?->level ?? 0) + 1 : 0;
                        $genealogy->save();
                    }
                }

                // Package et grade
                if ($request->has('package_id')) {
                    $data['package_id'] = $request->package_id;
                    if ($request->package_id) {
                        $package = Package::find($request->package_id);
                        if ($package) {
                            $rank = Rank::where('min_pv', '<=', $package->pv_value)
                                ->orderBy('level', 'desc')
                                ->first();
                            if ($rank) {
                                $data['rank_id'] = $rank->id;
                                $data['rank'] = $rank->name;
                                $data['rank_level'] = $rank->level;
                            }
                        }
                    }
                }

                if ($request->has('rank_id')) {
                    $data['rank_id'] = $request->rank_id;
                    if ($request->rank_id) {
                        $rank = Rank::find($request->rank_id);
                        if ($rank) {
                            $data['rank'] = $rank->name;
                            $data['rank_level'] = $rank->level;
                        }
                    }
                }

                if ($request->has('kyc_status')) {
                    $data['kyc_status'] = $request->kyc_status;
                }
            }

            $user->update($data);

            // Mettre à jour le rôle
            $newRole = $request->role;
            $currentRole = $user->roles->first()?->name ?? 'user';
            if ($currentRole !== $newRole) {
                $user->syncRoles([$newRole]);
            }

            DB::commit();

            Log::info('User updated', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'new_role' => $newRole,
            ]);

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', "Utilisateur {$user->name} mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', '❌ Erreur: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        DB::beginTransaction();

        try {
            if ($user->wallet) {
                $user->wallet->delete();
            }

            if ($user->genealogy) {
                $user->genealogy->delete();
            }

            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $parrain->decrement('total_sponsors');
                }
            }

            User::where('parrain_id', $user->id)->update(['parrain_id' => null]);

            $user->delete();

            DB::commit();

            Log::info('User deleted', [
                'user_id' => $id,
                'name' => $user->name,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.users')
                ->with('success', "Utilisateur {$user->name} supprimé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';

        Log::info('User ' . $status, [
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.users')
            ->with('success', "Utilisateur {$status} avec succès.");
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $newPassword = Str::random(10);

        $user->password = Hash::make($newPassword);
        $user->save();

        Log::info('Password reset', [
            'user_id' => $user->id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', "Mot de passe réinitialisé. Nouveau mot de passe: {$newPassword}");
    }

    public function assignPackage(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($id);
        $package = Package::find($request->package_id);

        $user->package_id = $package->id;
        $user->pv_balance += $package->pv_value;
        $user->bv_balance += $package->bv_value;
        $user->save();

        $newRank = $this->rankCalculator->calculateAdvancedRank($user);
        if ($newRank) {
            $user->rank_id = $newRank->id;
            $user->rank = $newRank->name;
            $user->save();
        }

        Log::info('Package assigned', [
            'user_id' => $user->id,
            'package_id' => $package->id,
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', "Package {$package->name} assigné à {$user->name}.");
    }

    private function generateSponsorCode(): string
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

    private function updateTeamCounters(User $user)
    {
        $currentUser = $user;
        $level = 0;

        while ($currentUser && $level < 10) {
            $parrain = User::find($currentUser->parrain_id);
            if (!$parrain) break;

            $parrain->increment('total_team');
            $currentUser = $parrain;
            $level++;
        }
    }

    private function updateTeamCountersDec(User $user)
    {
        $currentUser = $user;
        $level = 0;

        while ($currentUser && $level < 10) {
            $parrain = User::find($currentUser->parrain_id);
            if (!$parrain) break;

            $parrain->decrement('total_team');
            $currentUser = $parrain;
            $level++;
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('sponsor_id', 'like', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'email', 'sponsor_id', 'avatar']);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function export(Request $request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('rank')) {
            $query->where('rank_id', $request->rank);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Referral Code', 'Sponsor',
                'Rank', 'Level', 'Package', 'PV', 'BV', 'Monthly PV', 'Monthly BV',
                'Team PV', 'Team BV', 'Status', 'KYC', 'Role', 'Registration Date'
            ]);

            foreach ($users as $user) {
                $parrain = User::find($user->parrain_id);
                $role = $user->roles->first()?->name ?? 'user';

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '',
                    $user->sponsor_id ?? 'N/A',
                    $parrain?->name ?? 'None',
                    $user->rank?->name ?? 'Distributor',
                    $user->rank?->level ?? 1,
                    $user->package?->name ?? 'None',
                    $user->pv_balance ?? 0,
                    $user->bv_balance ?? 0,
                    $user->monthly_pv ?? 0,
                    $user->monthly_bv ?? 0,
                    $user->team_pv ?? 0,
                    $user->team_bv ?? 0,
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->kyc_status ?? 'Not submitted',
                    $role,
                    $user->created_at?->format('d/m/Y H:i') ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                if (User::where('email', $data['email'])->exists()) {
                    $errors[] = "Email {$data['email']} already exists";
                    continue;
                }

                $parrain = null;
                if (!empty($data['parrain_email'])) {
                    $parrain = User::where('email', $data['parrain_email'])->first();
                }

                $sponsorCode = $this->generateSponsorCode();

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make(Str::random(12)),
                    'sponsor_id' => $sponsorCode,
                    'parrain_id' => $parrain?->id,
                    'rank' => 'Distributeur',
                    'rank_level' => 1,
                    'is_active' => true,
                    'kyc_status' => 'not_submitted',
                ]);

                if (!Wallet::where('user_id', $user->id)->exists()) {
                    Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'pending_balance' => 0,
                        'currency' => 'USD',
                        'is_active' => true,
                    ]);
                }

                Genealogy::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $parrain?->id,
                    'parent_id' => $parrain?->id,
                    'level' => $parrain ? ($parrain->genealogy?->level ?? 0) + 1 : 0,
                ]);

                $user->assignRole('user');

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Error for {$data['email']}: " . $e->getMessage();
            }
        }

        fclose($handle);

        Log::info('User import', [
            'imported' => $imported,
            'errors' => count($errors),
            'admin_id' => auth()->id(),
        ]);

        $message = "{$imported} users imported successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " and " . (count($errors) - 5) . " more errors.";
            }
        }

        return redirect()->route('admin.users')
            ->with('success', $message);
    }
}