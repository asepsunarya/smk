<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 * 
 * Handles authentication for all user roles
 */
class AuthController extends Controller
{
    /**
     * Login user and create token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $profileData = null;
        if ($user->role === 'siswa' && $user->siswa) {
            $profileData = $user->siswa->load('kelas.jurusan');
        } elseif (in_array($user->role, ['guru', 'wali_kelas']) && $user->guru) {
            $profileData = $user->guru;
        }

        return response()->json([
            'user' => $user,
            'profile' => $profileData,
            'token' => $token,
            'role' => $user->role,
        ]);
    }

    /**
     * Register new user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,guru,wali_kelas,kepala_sekolah,siswa'],
            'nuptk' => ['nullable', 'string', 'unique:users'],
            'nis' => ['nullable', 'string', 'unique:users'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nuptk' => $request->nuptk,
            'nis' => $request->nis,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Logout user (revoke token).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();

        $profileData = null;
        if ($user->role === 'siswa' && $user->siswa) {
            $profileData = $user->siswa->load('kelas.jurusan');
        } elseif (in_array($user->role, ['guru', 'wali_kelas']) && $user->guru) {
            $profileData = $user->guru;
            
            if ($user->role === 'wali_kelas') {
                $profileData->load('user.kelasAsWali.siswa');
            }
        }

        return response()->json([
            'user' => $user,
            'profile' => $profileData,
        ]);
    }

    /**
     * Update password.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }
}