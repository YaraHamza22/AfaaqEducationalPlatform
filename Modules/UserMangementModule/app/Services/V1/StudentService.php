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

    public function list(array $filters, int $perPage = 15)
    {
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $cacheKey = "students_list_{$filtersKey}_limit_{$perPage}";

        return Cache::tags([self::TAG_GLOBAL])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($filters, $perPage) {
                return User::whereHas('studentProfile')
                    ->with(['media', 'studentProfile', 'roles.permissions'])
                    ->filters($filters)
                    ->paginate($perPage);
            }
        );
    }

    public function findById(int $id)
    {
        $cacheKey = "student_details_{$id}";

        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_STUDENT . $id])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($id) {
                return User::with(['media', 'studentProfile', 'roles.permissions'])
                    ->findOrFail($id);
            }
        );
    }

    public function create(array $data)
    {
        $studentDTO = StudentDTO::fromArray($data);

        return DB::transaction(function () use ($studentDTO) {
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
    }

    public function update(User $user, array $data)
    {
        $studentDTO = StudentDTO::fromArray($data);

        return DB::transaction(function () use ($studentDTO, $user) {
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
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->studentProfile()->delete();
            $user->delete();
        });

        Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_STUDENT . $user->id])->flush();
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
}