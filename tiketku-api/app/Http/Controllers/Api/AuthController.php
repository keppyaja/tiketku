<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $username = $request->input('username');
        if (! is_string($username) || trim($username) === '') {
            $name = $request->input('name');
            $base = is_string($name) ? Str::slug($name, '_') : 'user';
            $username = mb_substr($base !== '' ? $base : 'user', 0, 20)
                .'_'.Str::lower(Str::random(8));
            $request->merge(['username' => $username]);
        } else {
            $request->merge(['username' => mb_strtolower(trim($username))]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($data + ['role' => 'customer']);

        return response()->json([
            'data' => [
                'token' => $user->createToken('web')->plainTextToken,
                'user' => UserResource::make($user),
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        return response()->json([
            'data' => [
                'token' => $user->createToken('web')->plainTextToken,
                'user' => UserResource::make($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => UserResource::make($request->user())]);
    }
}
