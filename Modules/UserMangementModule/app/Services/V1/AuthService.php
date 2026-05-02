<?php

namespace Modules\UserMangementModule\Services\V1;

use Modules\UserMangementModule\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\StudentDTO;
use Modules\UserMangementModule\Enums\UserRole;

class AuthService
{
    public function register(array $data): array
    {
        $studentDTO = StudentDTO::fromArray($data);

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
                'redirect_to' => '/student/dashboard',
            ];
        });
    }

    public function login(array $credentials): array
    {
        $authCredentials = [
            'email' => $credentials['email'] ?? null,
            'password' => $credentials['password'] ?? null,
        ];

        if (! $token = JWTAuth::attempt($authCredentials)) {
            return [
                'status' => 'error',
                'message' => 'invalid credentials',
                'user' => null,
                'token' => null,
            ];
        }

        return [
            'status' => 'success',
            'user' => auth()->user(),
            'token' => $token,
        ];
    }
}