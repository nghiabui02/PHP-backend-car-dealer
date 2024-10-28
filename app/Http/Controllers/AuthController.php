<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->all();
        $attributes = Validator::make($data,[
            'login' => 'required', 'string',
            'password' => 'required', 'string',
        ]);

        if ($attributes->fails()) {
            return response()->json($attributes->messages(), 400);
        }

        $dataSend = $attributes->validated();

        if (filter_var($dataSend['login'], FILTER_VALIDATE_EMAIL)) {
            $loginType = 'email';
        } elseif (preg_match('/^[0-9]{10,15}$/', $dataSend['login'])) {
            $loginType = 'phone_number';
        } else {
            $loginType = 'username';
        }

        $credentials = [
            $loginType => $dataSend['login'],
            'password' => $dataSend['password'],
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

    /**
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->all();
        $attributes = Validator::make($data, [
            'email' => 'required', 'email', 'unique:users,email',
            'username' => 'required', 'string', 'unique:users,username', 'min:5', 'max:8',
            'password' => 'required', 'string', 'max:10', 'min:6', 'confirmed',
            'phone_number' => 'required', 'string',
            'first_name' => 'required', 'string',
            'last_name' => 'required', 'string',
        ]);
        if ($attributes->fails()) {
            return response()->json($attributes->messages(), 400);
        }
        $dataRegister = $attributes->validated();

        User::register($dataRegister);

        return response()->json(['message' => 'User registered successfully', 'user' => $attributes], 201);
    }
    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
