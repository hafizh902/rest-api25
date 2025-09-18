<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Remove role from request if empty string to avoid validation error
        if ($request->role === '') {
            $request->merge(['role' => null]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,penginput_data,pengunjung'
        ]);
        if ($validator->fails()) {
            Log::info('Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'pengunjung' // Default role if not provided
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User Berhasil Mendaftar',
            'data' => [
                'user' => $user,
                'token' => $this->respondWithToken($token)
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);

            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token gagal dibuat',
                'error' => $e->getMessage()
            ], 500);
        }


        $user = JWTAuth::user();
        \App\Models\LoginLog::create([
            'user_id' => $user->id,
            'login_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil login',
            'data' => $this->respondWithToken($token)
        ]);
    }
    
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil logout'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal logout, token tidak valid',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh(){
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
            'status' => 'success',
            'message' => 'Token telah diperbarui',
            'data' => $this->respondWithToken($newToken)
        ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui token',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    public function me(){
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'status' => 'success',
                'message' => 'User profile',
                'data' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau expired token',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    public function respondWithToken($token){
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expired_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }
}
