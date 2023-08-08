<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
//    public function login(Request $request) {
//
//        $user = User::where('login', $request->login)->first();
//
//        if($user) {
//            if( !password_verify($request->password, $user->password) ) {
//                return response()->json(['message' => __('Неверный логин или пароль')], 400);
//            }
//        }
//        else {
//            return response()->json(['message' => __('Неверный логин или пароль')], 400);
//        }
//
//        $token = $user->createToken($user->id, ['manager']);
//
//        return response()->json([
//            'token' => $token->plainTextToken,
//            'username' => $user->login
//        ], 200);
//    }
//
//    public function logout(Request $request) {
//        if($request->user()->currentAccessToken()->delete()) {
//            return response()->json([
//                'message' => 'success',
//            ], 200);
//        }
//        else {
//            return response()->json(['message' => 'Unauthorized--'], 401);
//        }
//    }
    public function login(Request $request) {
        $user = User::where('login', $request->login)->first();

        if($user) {
            if( !password_verify($request->password, $user->password) ) {
                return response()->json(['message' => __('Неверный логин или пароль')], 400);
            }
        }
        else {
            return response()->json(['message' => __('Неверный логин или пароль')], 400);
        }

        $token = $user->createToken($user->id, ['manager']);

        return response()->json([
            'token' => $token->plainTextToken,
            'username' => $user->login
        ], 200);
    }

    public function logout(Request $request) {
        if($request->user()->currentAccessToken()->delete()) {
            return response()->json([
                'message' => 'success',
            ], 200);
        }
        else {
            return response()->json(['message' => 'Unauthorized--'], 401);
        }
    }
}
