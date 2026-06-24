<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index()
    {
        // Si l'utilisateur est connecté, rediriger vers le dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // Vérifier si l'utilisateur a déjà vu l'onboarding
        $showOnboarding = !session()->has('onboarding_completed');
        
        if (!$showOnboarding) {
            return redirect()->route('login');
        }
        
        return view('onboarding.index');
    }
    
    public function complete(Request $request)
    {
        session()->put('onboarding_completed', true);
        return response()->json(['success' => true]);
    }
    
    public function skip()
    {
        session()->put('onboarding_completed', true);
        return redirect()->route('login');
    }
}
