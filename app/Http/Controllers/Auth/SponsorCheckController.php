<?php
// app/Http/Controllers/Auth/SponsorCheckController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SponsorCheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ]);

        $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();

        if ($sponsor) {
            return response()->json([
                'exists' => true,
                'name' => $sponsor->name,
                'email' => $sponsor->email,
                'sponsor_id' => $sponsor->sponsor_id,
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'No user found with this code.'
        ]);
    }
}