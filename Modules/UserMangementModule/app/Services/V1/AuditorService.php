<?php

namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\AuditorDTO;
use Modules\UserMangementModule\Enums\UserRole;
use Modules\UserMangementModule\Models\User;

class AuditorService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'auditors';
    private const TAG_PREFIX_AUDITOR = 'auditor_';
    private const CACHE_VERSION_KEY = 'auditors_cache_version';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "auditors_list_{$filtersKey}_limit_{$perPage}_v{$cacheVersion}";

        return $this->rememberWithTags([self::TAG_GLOBAL], $cacheKey, function () use ($perPage) {
            return User::whereHas('auditorProfile')
                ->with(['media', 'auditorProfile', 'roles.permissions'])
                ->paginate($perPage);
        });
    }

    public function findById(int $id)
    {
        $cacheVersion = $this->getCacheVersion();
        $cacheKey = "auditor_details_{$id}_v{$cacheVersion}";

        return $this->rememberWithTags(
            [self::TAG_GLOBAL, self::TAG_PREFIX_AUDITOR . $id],
            $cacheKey,
            function () use ($id) {
                return User::with(['media', 'auditorProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(AuditorDTO $auditorDTO)
    {
        $user = DB::transaction(function () use ($auditorDTO) {
            $userData = $auditorDTO->userData();
            $auditorData = $auditorDTO->auditorData();

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            if (isset($auditorDTO->avatar)) {
                $user->addMedia($auditorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->auditorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $auditorData
            );

            if (isset($auditorDTO->cv)) {
                $user->addMedia($auditorDTO->cv)->toMediaCollection('cv');
            }

            $user->assignRole(UserRole::AUDITOR->value);

            return $user->load(['media', 'auditorProfile', 'roles.permissions']);
        });

        $this->invalidateCache();

        return $user;
    }

    public function update(User $user, AuditorDTO $auditorDTO)
    {
        $updatedUser = DB::transaction(function () use ($auditorDTO, $user) {
            $user->update($auditorDTO->userData());

            if (isset($auditorDTO->avatar)) {
                $user->addMedia($auditorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->auditorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $auditorDTO->auditorData()
            );

            if (isset($auditorDTO->cv)) {
                $user->addMedia($auditorDTO->cv)->toMediaCollection('cv');
            }

            return $user->load(['media', 'auditorProfile', 'roles.permissions'])->refresh();
        });

        $this->invalidateCache($user->id);

        return $updatedUser;
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->auditorProfile()->delete();
            $user->delete();
        });

        $this->invalidateCache($user->id);
    }

    private function rememberWithTags(array $tags, string $cacheKey, callable $callback)
    {
        if (Cache::supportsTags()) {
            return Cache::tags($tags)->remember($cacheKey, self::CACHE_TTL, $callback);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    private function invalidateCache(?int $auditorId = null): void
    {
        if (Cache::supportsTags()) {
            $tags = [self::TAG_GLOBAL];
            if ($auditorId !== null) {
                $tags[] = self::TAG_PREFIX_AUDITOR . $auditorId;
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