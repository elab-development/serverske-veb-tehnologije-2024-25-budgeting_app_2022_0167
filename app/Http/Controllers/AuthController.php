<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $req)
    {
        $data = $req->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['sometimes','in:admin,user'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'] ?? 'user',
            'email_verified_at' => now(), // po želji ukloni ako hoćeš verifikaciju mejla
        ]);

        $token = $user->createToken('api', ['*'])->plainTextToken;

        return response()->json([
            'user'  => [
                'id' => $user->id, 'name'=>$user->name, 'email'=>$user->email, 'role'=>$user->role
            ],
            'token' => $token,
        ], 201);
    }

    // POST /api/login
    public function login(Request $req)
    {
        $data = $req->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('api', ['*'])->plainTextToken;

        return response()->json([
            'user'  => [
                'id' => $user->id, 'name'=>$user->name, 'email'=>$user->email, 'role'=>$user->role
            ],
            'token' => $token,
        ]);
    }

    // GET /api/me
    public function me(Request $req)
    {
        $u = $req->user();
        return response()->json([
            'id'=>$u->id, 'name'=>$u->name, 'email'=>$u->email, 'role'=>$u->role
        ]);
    }

    // POST /api/logout  (samo trenutni token)
    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
 
}
