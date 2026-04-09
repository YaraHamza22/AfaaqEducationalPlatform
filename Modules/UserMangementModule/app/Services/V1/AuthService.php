<?php

namespace Modules\UserMangementModule\Services\V1;

use Modules\UserMangementModule\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\StudentDTO;
use Modules\UserMangementModule\Enums\UserRole;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function register($data)
    {
        $studentDTO = StudentDTO::fromArray($data);
        \Log::info('REGISTER DATA', $data);
        \Log::info('STUDENT DTO DATA',$studentDTO->studentData());

      //      $user = User::create($data);
        //    if (! $token = JWTAuth::fromUser($user)) {
          //      return [
            //        'status'=>'error',
              //      'user'=>null,
                //    'token'=>null
                //];
            //}
            //return [
              //  'status'=>'success',
                //'user'=>$user,
                //'token'=>$token,
            //];
            return DB::transaction(function () use ($data, $studentDTO) {
                $userData = $studentDTO->userData();
                $studentData = $studentDTO->studentData();
                $userData['password'] = Hash::make($data['password']);
                $user = User::create($userData);
                $user->studentProfile()->create($studentData);
                $user->assignRole(UserRole::STUDENT->value);
                $token = JWTAuth::fromUser($user);
                $user->load([
                    'roles.permissions',
                    'studentProfile',
                ]);

            return [
                'status' => 'success',
                'user' => $user,
                'token' => $token,
                'redirect_to' => '/student/dashboard'
            ];
            });
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