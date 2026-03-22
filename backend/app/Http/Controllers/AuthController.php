<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        return response()->json([
            'access_token'  =>  $token,
            'token_type'    =>  'Bearer',
            'user' => Auth::guard('api')->user(),
        ]);
    }

    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }
}
