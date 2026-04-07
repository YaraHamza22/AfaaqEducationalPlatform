<?php

namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserMangementModule\DTOs\InstructorDTO;
use Modules\UserMangementModule\Enums\UserRole;
use Modules\UserMangementModule\Models\User;

class InstructorService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'instructors';
    private const TAG_PREFIX_INSTRUCTOR = 'instructor_';

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheKey = "instructors_list_{$filtersKey}_limit_{$perPage}";

        return Cache::tags([self::TAG_GLOBAL])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($filters, $perPage) {
                return User::whereHas('instructorProfile')
                    ->with(['media', 'instructorProfile', 'roles.permissions'])
                    ->filters($filters)
                    ->paginate($perPage);
            }
        );
    }

    public function findById(int $id)
    {
        $cacheKey = "instructor_details_{$id}";

        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_INSTRUCTOR . $id])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($id) {
                return User::with(['media', 'instructorProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(array $data)
    {
        $instructorDTO = InstructorDTO::fromArray($data);

        return DB::transaction(function () use ($instructorDTO) {
            $userData = $instructorDTO->userData();
            $instructorData = $instructorDTO->instructorData();

            $user = User::create($userData);

            if (isset($instructorDTO->avatar)) {
                $user->addMedia($instructorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->instructorProfile()->create($instructorData);

            $user->assignRole(UserRole::INSTRUCTOR->value);

            return $user->load(['media', 'instructorProfile', 'roles.permissions']);
        });
    }

    public function update(User $user, array $data)
    {
        $instructorDTO = InstructorDTO::fromArray($data);

        return DB::transaction(function () use ($instructorDTO, $user) {
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
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->instructorProfile()->delete();
            $user->delete();
        });

        Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_INSTRUCTOR . $user->id])->flush();
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

        $user->assignRole(UserRole::INSTRUCTOR->value);

        return $user->load(['media', 'instructorProfile', 'roles.permissions']);
    }
}