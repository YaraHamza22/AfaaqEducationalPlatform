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

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheKey = "auditors_list_{$filtersKey}_limit_{$perPage}";

        return Cache::tags([self::TAG_GLOBAL])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($perPage) {
                return User::whereHas('auditorProfile')
                    ->with(['media', 'auditorProfile', 'roles.permissions'])
                    ->paginate($perPage);
            }
        );
    }

    public function findById(int $id)
    {
        $cacheKey = "auditor_details_{$id}";

        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_AUDITOR . $id])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($id) {
                return User::with(['media', 'auditorProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(AuditorDTO $auditorDTO)
    {
        return DB::transaction(function () use ($auditorDTO) {
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
    }

    public function update(User $user, AuditorDTO $auditorDTO)
    {
        return DB::transaction(function () use ($auditorDTO, $user) {
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
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->auditorProfile()->delete();
            $user->delete();
        });

        Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_AUDITOR . $user->id])->flush();
    }
}