<?php

namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\InstructorDTO;
use Modules\UserMangementModule\Enums\UserRole;
use Modules\UserMangementModule\Models\User;
use Spatie\Permission\Models\Role;

class InstructorService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'instructors';
    private const TAG_PREFIX_INSTRUCTOR = 'instructor_';
    private const CACHE_VERSION_KEY = 'instructors_cache_version';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "instructors_list_{$filtersKey}_limit_{$perPage}_v{$cacheVersion}";

        return $this->rememberWithTags([self::TAG_GLOBAL], $cacheKey, function () use ($filters, $perPage) {
            return User::whereHas('instructorProfile')
                ->with(['media', 'instructorProfile', 'roles.permissions'])
                ->filters($filters)
                ->paginate($perPage);
        });
    }

    public function findById(int $id)
    {
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "instructor_details_{$id}_v{$cacheVersion}";

        return $this->rememberWithTags(
            [self::TAG_GLOBAL, self::TAG_PREFIX_INSTRUCTOR . $id],
            $cacheKey,
            function () use ($id) {
                return User::with(['media', 'instructorProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(array $data)
    {
        $instructorDTO = InstructorDTO::fromArray($data);

        $user = DB::transaction(function () use ($instructorDTO) {
            $userData = $instructorDTO->userData();
            $instructorData = $instructorDTO->instructorData();

            $user = User::create($userData);

            if (isset($instructorDTO->avatar)) {
                $user->addMedia($instructorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->instructorProfile()->create($instructorData);
            $this->ensureInstructorRoleExists();
            $user->assignRole(UserRole::INSTRUCTOR->value);

            return $user->load(['media', 'instructorProfile', 'roles.permissions']);
        });

        $this->invalidateCache();

        return $user;
    }

    public function update(User $user, array $data)
    {
        $instructorDTO = InstructorDTO::fromArray($data);

        $updatedUser = DB::transaction(function () use ($instructorDTO, $user) {
            $user->update($instructorDTO->userData());

            if (isset($instructorDTO->avatar)) {
                $user->clearMediaCollection('avatar');
                $user->addMedia($instructorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->instructorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $instructorDTO->instructorData()
            );

            return $user->load(['media', 'instructorProfile', 'roles.permissions'])->refresh();
        });

        $this->invalidateCache($user->id);

        return $updatedUser;
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->instructorProfile()->delete();
            $user->delete();
        });

        $this->invalidateCache($user->id);
    }

    public function fillProfileInfo(array $data)
    {
        if (!auth()->check()) {
            return [
                'message' => 'please sign in',
            ];
        }

        $user = auth()->user();

        $user->instructorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        $this->ensureInstructorRoleExists();
        $user->assignRole(UserRole::INSTRUCTOR->value);

        return $user->load(['media', 'instructorProfile', 'roles.permissions']);
    }

    private function ensureInstructorRoleExists(): void
    {
        Role::firstOrCreate([
            'name' => UserRole::INSTRUCTOR->value,
            'guard_name' => 'api',
        ]);
    }

    private function rememberWithTags(array $tags, string $cacheKey, callable $callback)
    {
        if (Cache::supportsTags()) {
            return Cache::tags($tags)->remember($cacheKey, self::CACHE_TTL, $callback);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    private function invalidateCache(?int $instructorId = null): void
    {
        if (Cache::supportsTags()) {
            $tags = [self::TAG_GLOBAL];
            if ($instructorId !== null) {
                $tags[] = self::TAG_PREFIX_INSTRUCTOR . $instructorId;
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