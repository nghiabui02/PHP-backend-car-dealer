<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $attributes = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (filter_var($attributes['login'], FILTER_VALIDATE_EMAIL)) {
            $loginType = 'email';
        } elseif (preg_match('/^[0-9]{10,15}$/', $attributes['login'])) {
            $loginType = 'phone_number';
        } else {
            $loginType = 'username';
        }

        $credentials = [
            $loginType => $attributes['login'],
            'password' => $attributes['password'],
        ];

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Login info or password was wrong'], 401);
            }
            $user = auth()->user();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        return response()->json(['user' => $user ,'access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function register(Request $request): JsonResponse
    {
        $attributes = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username', 'min:5', 'max:8'],
            'password' => ['required', 'string', 'max:10', 'min:6', 'confirmed'],
            'name' => ['required', 'string', 'max:10'],
            'phone_number' => ['required', 'string'],
        ]);

        User::register($attributes);

        return response()->json(['message' => 'User registered successfully', 'user' => $attributes], 201);
    }
    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
