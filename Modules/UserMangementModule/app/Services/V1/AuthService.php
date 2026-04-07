<?php

namespace Modules\UserMangementModule\Services\V1;

use Modules\UserMangementModule\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register($data)
    {
            $user = User::create($data);
            if (! $token = JWTAuth::fromUser($user)) {
                return [
                    'status'=>'error',
                    'user'=>null,
                    'token'=>null
                ];
            }
            return [
                'status'=>'success',
                'user'=>$user,
                'token'=>$token,
            ];
    }

    public function login($credentials){
       if (! $token = JWTAuth::attempt($credentials)) {
            return [
                'status'=>'error',
                'user'=>null,
                'token'=>null
            ];
        }
        return [
            'status'=>'success',
            'user'=>auth()->user(),
            'token'=>$token
        ];
    }
}