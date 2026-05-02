<?php

namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\StudentDTO;
use Modules\UserMangementModule\Enums\UserRole;
use Modules\UserMangementModule\Models\User;

class StudentService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'students';
    private const TAG_PREFIX_STUDENT = 'student_';
    private const CACHE_VERSION_KEY = 'students_cache_version';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "students_list_{$filtersKey}_limit_{$perPage}_v{$cacheVersion}";

        return $this->rememberWithTags([self::TAG_GLOBAL], $cacheKey, function () use ($filters, $perPage) {
            return User::whereHas('studentProfile')
                ->with(['media', 'studentProfile', 'roles.permissions'])
                ->filters($filters)
                ->paginate($perPage);
        });
    }

    public function findById(int $id)
    {
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "student_details_{$id}_v{$cacheVersion}";

        return $this->rememberWithTags(
            [self::TAG_GLOBAL, self::TAG_PREFIX_STUDENT . $id],
            $cacheKey,
            function () use ($id) {
                return User::with(['media', 'studentProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(array $data)
    {
        $studentDTO = StudentDTO::fromArray($data);

        $user = DB::transaction(function () use ($studentDTO) {
            $userData = $studentDTO->userData();
            $studentData = $studentDTO->studentData();

            $user = User::create($userData);

            if (isset($studentDTO->avatar)) {
                $user->addMedia($studentDTO->avatar)->toMediaCollection('avatar');
            }

            $user->studentProfile()->create($studentData);
            $user->assignRole(UserRole::STUDENT->value);

            return $user->load(['media', 'studentProfile', 'roles.permissions']);
        });

        $this->invalidateCache();

        return $user;
    }

    public function update(User $user, array $data)
    {
        $studentDTO = StudentDTO::fromArray($data);

        $updatedUser = DB::transaction(function () use ($studentDTO, $user) {
            $user->update($studentDTO->userData());

            if (isset($studentDTO->avatar)) {
                $user->clearMediaCollection('avatar');
                $user->addMedia($studentDTO->avatar)->toMediaCollection('avatar');
            }

            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $studentDTO->studentData()
            );

            return $user->load(['media', 'studentProfile', 'roles.permissions'])->refresh();
        });

        $this->invalidateCache($user->id);

        return $updatedUser;
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->studentProfile()->delete();
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

        $user->studentProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        $user->assignRole(UserRole::STUDENT->value);

        return $user->load(['media', 'studentProfile', 'roles.permissions']);
    }

    private function rememberWithTags(array $tags, string $cacheKey, callable $callback)
    {
        if (Cache::supportsTags()) {
            return Cache::tags($tags)->remember($cacheKey, self::CACHE_TTL, $callback);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    private function invalidateCache(?int $studentId = null): void
    {
        if (Cache::supportsTags()) {
            $tags = [self::TAG_GLOBAL];
            if ($studentId !== null) {
                $tags[] = self::TAG_PREFIX_STUDENT . $studentId;
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