<?php
// app/Http/Controllers/Auth/EmailCheckController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailCheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $exists = User::where('email', $email)->exists();

        if ($exists) {
            return response()->json([
                'exists' => true,
                'available' => false,
                'type' => 'error',
                'title' => 'Email unavailable',
                'message' => 'This email address is already associated with an existing account.',
                'detail' => 'Please use another email address or log in.',
                'field_status' => 'error'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'exists' => false,
                'available' => false,
                'type' => 'warning',
                'title' => 'Invalid format',
                'message' => 'The email address entered is not valid.',
                'detail' => 'Expected format: name@domain.com',
                'field_status' => 'warning'
            ]);
        }

        return response()->json([
            'exists' => false,
            'available' => true,
            'type' => 'success',
            'title' => 'Email available',
            'message' => 'This email address is available for registration.',
            'detail' => 'You can continue the registration process.',
            'field_status' => 'success'
        ]);
    }
}