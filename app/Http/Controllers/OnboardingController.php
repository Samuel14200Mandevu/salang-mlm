<?php
// app/Http/Controllers/OnboardingController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

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