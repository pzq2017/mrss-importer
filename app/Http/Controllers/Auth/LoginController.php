<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->validateWithException($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);
        if (! $token = JWTAuth::attempt($credentials)) {
            return $this->responseErrorWithMessage('wrong email or password.');
        } else {
            $payload  = JWTAuth::decode(new Token($token));
            $user = Auth::user();
            $user->token = $token;
            $user->expire_at = date('Y-m-d H:i:s', $payload->get('exp'));
            return new UserResource($user);
        }
    }

    public function logout()
    {
        Auth::logout();
        return $this->responseSuccessWithMessage();
    }

    public function refresh()
    {
        if (! $token = Auth::refresh()) {
            return $this->responseErrorWithMessage('Token generation failed.');
        } else {
            $payload  = JWTAuth::decode(new Token($token));
            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'expire_at' => date('Y-m-d H:i:s', $payload->get('exp')),
                ]
            ]);
        }
    }
}
