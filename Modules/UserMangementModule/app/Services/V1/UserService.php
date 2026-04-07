<?php

namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\Enums\UserRole;
use Modules\UserMangementModule\Models\User;
use Modules\UserMangementModule\Transformers\UserResource;

class UserService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'users';
    private const TAG_PREFIX_USER = 'user_';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheKey = "users_list_{$filtersKey}_limit_{$perPage}";

        return Cache::tags([self::TAG_GLOBAL])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($filters, $perPage) {
                return User::with([
                    'roles.permissions',
                    'studentProfile',
                    'instructorProfile',
                    'auditorProfile',
                ])
                ->filter($filters)
                ->paginate($perPage);
            }
        );
    }

    public function findById(int $id)
    {
        $cacheKey = "user_details_{$id}";

        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_USER . $id])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($id) {
                $user = User::with([
                    'roles.permissions',
                    'studentProfile',
                    'instructorProfile',
                    'auditorProfile',
                ])->findOrFail($id);

                return new UserResource($user);
            }
        );
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            if (isset($data['role'])) {
                $user->assignRole($data['role']);
            }

            if (isset($data['avatar'])) {
                $user->addMedia($data['avatar'])->toMediaCollection('avatar');
            }

            return $user->load([
                'roles.permissions',
                'studentProfile',
                'instructorProfile',
                'auditorProfile',
            ]);
        });
    }

    public function update(User $user, array $data)
    {
        $user->update($data);

        if (isset($data['avatar'])) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }

        return $user->load([
            'roles.permissions',
            'studentProfile',
            'instructorProfile',
            'auditorProfile',
        ])->refresh();
    }

    public function delete(User $user): void
    {
        Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_USER . $user->id])->flush();
        $user->delete();
    }

    private function loadProfile(User $user, array $roles)
    {
        $profileMap = [
            UserRole::INSTRUCTOR->value => 'instructorProfile',
            UserRole::AUDITOR->value => 'auditorProfile',
            UserRole::STUDENT->value => 'studentProfile',
        ];

        foreach ($profileMap as $role => $profile) {
            if (in_array($role, $roles)) {
                $user->load($profile);
            }
        }

        return $user;
    }
}