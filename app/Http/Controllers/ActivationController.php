<?php
// app/Http/Controllers/ActivationController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Notifications\ActivationCodeNotification;
use App\Services\SmsService;
use App\Services\MLM\CommissionDistributor;
use App\Services\MLM\AdvancedRankCalculator;
use App\Models\CommissionPeriod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Jobs\UpdateTeamPV;
use App\Jobs\UpdateRanks;
use App\Jobs\CalculatePVBV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivationController extends Controller
{
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est deja actif.');
        }

        $packages = Package::where('is_active', true)->get();

        return view('auth.activate', compact('user', 'packages'));
    }

    public function activateWithCode(Request $request)
    {
        $request->validate([
            'activation_code' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est deja actif.');
        }

        if ($user->activation_code !== $request->activation_code) {
            return back()->with('error', 'Code d\'activation invalide.');
        }

        if ($user->activation_code_expires_at < now()) {
            return back()->with('error', 'Code d\'activation expire. Veuillez contacter l\'administrateur.');
        }

        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        DB::beginTransaction();

        try {
            // =============================================
            // AJOUT: Créditer le parrain en PV (10%)
            // =============================================
            $sponsorPV = 0;
            $sponsorName = null;
            
            if ($package && $user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor && $sponsor->is_active) {
                    $sponsorPV = $package->pv_value * 0.10;
                    $sponsorBV = $package->bv_value * 0.10;
                    
                    $sponsor->pv_balance += $sponsorPV;
                    $sponsor->monthly_pv += $sponsorPV;
                    $sponsor->bv_balance += $sponsorBV;
                    $sponsor->monthly_bv += $sponsorBV;
                    $sponsor->saveQuietly();
                    $sponsorName = $sponsor->name;
                    
                    Log::info('Parrain credite en PV lors de l activation par code', [
                        'parrain_id' => $sponsor->id,
                        'parrain_name' => $sponsor->name,
                        'pv_credited' => $sponsorPV,
                        'filleul_id' => $user->id,
                        'filleul_name' => $user->name,
                        'package' => $package->name,
                    ]);
                }
            }

            // Créditer le nouveau membre
            if ($package) {
                $user->addPV($package->pv_value, 'activation', $package->id);
                $user->package_id = $package->id;
            }

            $user->is_active = true;
            $user->activated_at = now();
            $user->activation_method = 'code';
            $user->activation_code = null;
            $user->activation_code_expires_at = null;
            $user->save();

            DB::commit();

            // Distribuer les commissions
            if ($package) {
                $this->calculateCommissionsForPackage($user, $package);
                
                // =============================================
                // AJOUT: Mettre à jour le grade du parrain
                // =============================================
                if ($user->parrain_id) {
                    $sponsor = User::find($user->parrain_id);
                    if ($sponsor) {
                        $this->rankCalculator->recalculateUserRank($sponsor, 'Activation filleul');
                    }
                }
                
                dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
                dispatch(new UpdateRanks($user->id))->onQueue('high');
                dispatch(new CalculatePVBV($user->id))->onQueue('high');
                
                if ($user->parrain_id) {
                    dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                    dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                }
                
                Cache::forget("descendants_{$user->id}");
                Cache::forget("descendants_count_{$user->id}");
                Cache::forget("user_rank_{$user->id}");
            }

            $user->refresh();
            $rankName = $user->rank ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;

            Log::info('User activated with code', [
                'user_id' => $user->id,
                'email' => $user->email,
                'package_id' => $package?->id,
                'package_name' => $package?->name,
                'rank' => $rankName,
                'rank_level' => $rankLevel,
            ]);

            $message = "Votre compte a ete active avec succes !";
            $message .= "\nGrade: {$rankName} (Niv. {$rankLevel})";
            
            if ($package && $sponsorPV > 0 && $sponsorName) {
                $message .= "\n\nVotre parrain {$sponsorName} a recu " . number_format($sponsorPV, 1) . " PV (10%)";
            }

            return redirect()->route('dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur activation avec code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function activateWithPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est deja actif.');
        }

        $package = Package::find($request->package_id);

        DB::beginTransaction();
        try {
            // =============================================
            // AJOUT: Créditer le parrain en PV (10%)
            // =============================================
            $sponsorPV = 0;
            $sponsorName = null;
            
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor && $sponsor->is_active) {
                    $sponsorPV = $package->pv_value * 0.10;
                    $sponsorBV = $package->bv_value * 0.10;
                    
                    $sponsor->pv_balance += $sponsorPV;
                    $sponsor->monthly_pv += $sponsorPV;
                    $sponsor->bv_balance += $sponsorBV;
                    $sponsor->monthly_bv += $sponsorBV;
                    $sponsor->saveQuietly();
                    $sponsorName = $sponsor->name;
                    
                    Log::info('Parrain credite en PV lors de l activation avec package public', [
                        'parrain_id' => $sponsor->id,
                        'parrain_name' => $sponsor->name,
                        'pv_credited' => $sponsorPV,
                        'filleul_id' => $user->id,
                        'filleul_name' => $user->name,
                        'package' => $package->name,
                    ]);
                }
            }

            // Créditer le nouveau membre
            $user->update([
                'is_active' => true,
                'activated_at' => now(),
                'activation_method' => 'package',
                'package_id' => $package->id,
                'pv_balance' => ($user->pv_balance ?? 0) + $package->pv_value,
                'bv_balance' => ($user->bv_balance ?? 0) + $package->bv_value,
                'monthly_pv' => ($user->monthly_pv ?? 0) + $package->pv_value,
                'monthly_bv' => ($user->monthly_bv ?? 0) + $package->bv_value,
                'activation_code' => null,
                'activation_code_expires_at' => null,
            ]);

            $this->calculateCommissionsForPackage($user, $package);

            DB::commit();

            // =============================================
            // AJOUT: Mettre à jour le grade du parrain
            // =============================================
            if ($user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor) {
                    $this->rankCalculator->recalculateUserRank($sponsor, 'Activation filleul');
                }
            }

            dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
            dispatch(new UpdateRanks($user->id))->onQueue('high');
            dispatch(new CalculatePVBV($user->id))->onQueue('high');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("user_rank_{$user->id}");

            $user->refresh();
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $rankName = $user->rank ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;
            $packageName = $package->name;

            Log::info('User activated with package and rank updated', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $packageName,
                'new_rank' => $rankName,
                'rank_level' => $rankLevel,
            ]);

            $message = "Votre compte a ete active avec succes !";
            $message .= "\nPackage: {$packageName}";
            $message .= "\nGrade: {$rankName} (Niv. {$rankLevel})";
            $message .= "\nPV Total: " . number_format($user->pv_balance, 1, ',', ' ');
            
            if ($sponsorPV > 0 && $sponsorName) {
                $message .= "\n\nVotre parrain {$sponsorName} a recu " . number_format($sponsorPV, 1) . " PV (10%)";
            }

            return redirect()->route('dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur activation avec package', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function activateWithLink($code)
    {
        $user = User::where('activation_code', $code)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Code d\'activation invalide.');
        }

        if ($user->is_active) {
            return redirect()->route('login')->with('info', 'Ce compte est deja actif.');
        }

        if ($user->activation_code_expires_at < now()) {
            return redirect()->route('login')->with('error', 'Code d\'activation expire. Veuillez contacter l\'administrateur.');
        }

        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        DB::beginTransaction();

        try {
            // =============================================
            // AJOUT: Créditer le parrain en PV (10%)
            // =============================================
            $sponsorPV = 0;
            $sponsorName = null;
            
            if ($package && $user->parrain_id) {
                $sponsor = User::find($user->parrain_id);
                if ($sponsor && $sponsor->is_active) {
                    $sponsorPV = $package->pv_value * 0.10;
                    $sponsorBV = $package->bv_value * 0.10;
                    
                    $sponsor->pv_balance += $sponsorPV;
                    $sponsor->monthly_pv += $sponsorPV;
                    $sponsor->bv_balance += $sponsorBV;
                    $sponsor->monthly_bv += $sponsorBV;
                    $sponsor->saveQuietly();
                    $sponsorName = $sponsor->name;
                    
                    Log::info('Parrain credite en PV lors de l activation par lien', [
                        'parrain_id' => $sponsor->id,
                        'parrain_name' => $sponsor->name,
                        'pv_credited' => $sponsorPV,
                        'filleul_id' => $user->id,
                        'filleul_name' => $user->name,
                        'package' => $package->name,
                    ]);
                }
            }

            // Créditer le nouveau membre
            $updateData = [
                'is_active' => true,
                'activated_at' => now(),
                'activation_method' => 'link',
                'activation_code' => null,
                'activation_code_expires_at' => null,
            ];

            if ($package) {
                $updateData['package_id'] = $package->id;
                $updateData['pv_balance'] = ($user->pv_balance ?? 0) + $package->pv_value;
                $updateData['bv_balance'] = ($user->bv_balance ?? 0) + $package->bv_value;
                $updateData['monthly_pv'] = ($user->monthly_pv ?? 0) + $package->pv_value;
                $updateData['monthly_bv'] = ($user->monthly_bv ?? 0) + $package->bv_value;
            }

            $user->update($updateData);

            DB::commit();

            if ($package) {
                $this->calculateCommissionsForPackage($user, $package);
                
                // =============================================
                // AJOUT: Mettre à jour le grade du parrain
                // =============================================
                if ($user->parrain_id) {
                    $sponsor = User::find($user->parrain_id);
                    if ($sponsor) {
                        $this->rankCalculator->recalculateUserRank($sponsor, 'Activation filleul');
                    }
                }
                
                dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
                dispatch(new UpdateRanks($user->id))->onQueue('high');
                dispatch(new CalculatePVBV($user->id))->onQueue('high');
                
                if ($user->parrain_id) {
                    dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                    dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                }
                
                Cache::forget("descendants_{$user->id}");
                Cache::forget("descendants_count_{$user->id}");
                Cache::forget("user_rank_{$user->id}");
            }

            $user->refresh();
            $rankName = $user->rank ?? 'Distributeur';

            Log::info('User activated with link', [
                'user_id' => $user->id,
                'email' => $user->email,
                'package_id' => $package?->id,
                'rank' => $rankName,
            ]);

            $message = "Votre compte a ete active avec succes !";
            $message .= "\nGrade: {$rankName}";
            
            if ($package && $sponsorPV > 0 && $sponsorName) {
                $message .= "\n\nVotre parrain {$sponsorName} a recu " . number_format($sponsorPV, 1) . " PV (10%)";
            }
            $message .= "\nVous pouvez maintenant vous connecter.";

            return redirect()->route('login')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur activation avec lien', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('login')->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function resendCode(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        if ($user->is_active) {
            return redirect()->route('dashboard')->with('info', 'Votre compte est deja actif.');
        }

        $method = $request->input('method', 'email');

        $cacheKey = "resend_code_{$user->id}_{$method}_" . date('Y-m-d');
        $resendCount = Cache::get($cacheKey, 0);

        if ($resendCount >= 3) {
            return back()->with('error', "Vous avez deja demande 3 codes par {$method} aujourd'hui.");
        }

        $newCode = 'ACT-' . strtoupper(substr(md5(uniqid() . time()), 0, 12));
        
        $user->update([
            'activation_code' => $newCode,
            'activation_code_expires_at' => now()->addDays(7),
        ]);

        $package = null;
        if ($user->activation_package_id) {
            $package = Package::find($user->activation_package_id);
        }

        $success = false;

        try {
            if ($method === 'email') {
                $user->notify(new ActivationCodeNotification($newCode, $package));
                $success = true;
                $message = 'Un nouveau code a ete envoye a votre adresse email.';
            } 
            elseif ($method === 'sms') {
                $request->validate([
                    'phone' => 'required|string|max:20',
                ]);

                $user->phone = $request->phone;
                $user->save();

                $smsService = app(SmsService::class);
                $provider = $smsService->detectProvider($request->phone);
                
                $smsSent = $smsService->sendActivationCode($request->phone, $newCode, $provider);

                if ($smsSent) {
                    $success = true;
                    $providerName = ucfirst($provider);
                    $message = "Un nouveau code a ete envoye par SMS via {$providerName} a votre numero de telephone.";
                } else {
                    throw new \Exception('Erreur lors de l\'envoi du SMS.');
                }
            }

            if ($success) {
                Cache::put($cacheKey, $resendCount + 1, now()->addDay());
                return back()->with('success', $message);
            }

        } catch (\Exception $e) {
            Log::error('Error resending activation code: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'method' => $method,
            ]);
            return back()->with('error', 'Erreur lors de l\'envoi du code. Veuillez reessayer.');
        }

        return back()->with('error', 'Methode non supportee.');
    }

    private function calculateCommissionsForPackage($user, $package)
    {
        try {
            $period = CommissionPeriod::firstOrCreate(
                ['period' => date('Y-m')],
                [
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'PKG-' . strtoupper(uniqid()),
                'subtotal' => $package->price,
                'tax' => 0,
                'shipping' => 0,
                'discount' => 0,
                'total' => $package->price,
                'status' => 'completed',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'package_id' => $package->id,
                'name' => $package->name,
                'sku' => 'PKG-' . $package->slug,
                'quantity' => 1,
                'price' => $package->price,
                'total' => $package->price,
                'pv_value' => $package->pv_value,
                'bv_value' => $package->bv_value,
                'options' => json_encode([
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                ]),
            ]);

            $commissionDistributor = app(CommissionDistributor::class);
            $commissions = $commissionDistributor->distributeCommissions(
                $user,
                $package,
                $order->id,
                $period
            );

            $totalAmount = collect($commissions)->sum('amount');

            Log::info('Commissions calculees pour l\'activation', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'commissions_count' => count($commissions),
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des commissions', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}