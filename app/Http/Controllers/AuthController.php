<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'full_name' => 'required',
            'username' => 'required|unique:users,username|min:3|regex:/^[a-zA-Z0-9_.]+$/',
            'password' => 'required|min:6',
            'bio' => 'required|max:100',
            'is_private' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'is_private' => $request->is_private
        ]);


        return response()->json([
            'message' => 'Register success',
            'Token' => "Soon",
            'User' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|unique:users,username|min:3|regex:/^[a-zA-Z0-9_.]+$/',
            'password' => 'required|min:6',
        ];

        Validator::make($request->all(), $rules);

        if (!Auth::attempt($request->only(['username', 'password']))) {
            return response()->json([
                'message' => 'Wrong username or password',
            ], 401);
        }

        $user = User::where('username', $request->username)->first();


        return response()->json([
            'message' => 'Login success',
            'token' => $user->createToken('Facegram')->plainTextToken,
            "user" => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                "message" => "Logout success",
            ], 200);
        } else {
            return response()->json([
                "message" => "Unauthenticated.",
            ], 401);
        }
    }
}