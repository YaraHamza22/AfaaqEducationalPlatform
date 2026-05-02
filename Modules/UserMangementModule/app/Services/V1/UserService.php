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
    private const CACHE_VERSION_KEY = 'users_cache_version';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "users_list_{$filtersKey}_limit_{$perPage}_v{$cacheVersion}";

        return $this->rememberWithTags([self::TAG_GLOBAL], $cacheKey, function () use ($filters, $perPage) {
            return User::with([
                'roles.permissions',
                'studentProfile',
                'instructorProfile',
                'auditorProfile',
            ])
                ->filter($filters)
                ->paginate($perPage);
        });
    }

    public function findById(int $id)
    {
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "user_details_{$id}_v{$cacheVersion}";

        return $this->rememberWithTags(
            [self::TAG_GLOBAL, self::TAG_PREFIX_USER . $id],
            $cacheKey,
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
        $user = DB::transaction(function () use ($data) {
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

        $this->invalidateCache();

        return $user;
    }

    public function update(User $user, array $data)
    {
        $user->update($data);

        if (isset($data['avatar'])) {
            $user->clearMediaCollection('avatar');
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }

        $updatedUser = $user->load([
            'roles.permissions',
            'studentProfile',
            'instructorProfile',
            'auditorProfile',
        ])->refresh();

        $this->invalidateCache($user->id);

        return $updatedUser;
    }

    public function delete(User $user): void
    {
        $this->invalidateCache($user->id);
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
            if (in_array($role, $roles, true)) {
                $user->load($profile);
            }
        }

        return $user;
    }

    private function rememberWithTags(array $tags, string $cacheKey, callable $callback)
    {
        if (Cache::supportsTags()) {
            return Cache::tags($tags)->remember($cacheKey, self::CACHE_TTL, $callback);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    private function invalidateCache(?int $userId = null): void
    {
        if (Cache::supportsTags()) {
            $tags = [self::TAG_GLOBAL];
            if ($userId !== null) {
                $tags[] = self::TAG_PREFIX_USER . $userId;
            }

            Cache::tags($tags)->flush();
            return;
        }

        $this->bumpCacheVersion();
    }

    private function getCacheVersion(): int
    {
        return (int) Cache::rememberForever(self::CACHE_VERSION_KEY, fn () => 1);
    }

    private function bumpCacheVersion(): void
    {
        if (!Cache::has(self::CACHE_VERSION_KEY)) {
            Cache::forever(self::CACHE_VERSION_KEY, 1);
        }

        Cache::increment(self::CACHE_VERSION_KEY);
    }
}