<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        return response()->json(['message' => 'Login endpoint']);
    }

    public function register(Request $request)
    {
        return response()->json(['message' => 'Register endpoint']);
    }

    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logout endpoint']);
    }

    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}
