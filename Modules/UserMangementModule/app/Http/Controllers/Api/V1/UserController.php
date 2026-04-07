<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserMangementModule\Services\V1\UserService;
use Modules\UserMangementModule\Http\Requests\Api\V1\User\UserFilterRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\User\UserStoreRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\User\UserUpdateRequest;
use Modules\UserMangementModule\Models\User;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        $this->middleware('permission:list-users')->only('index');
        $this->middleware('permission:show-user')->only('show');
        $this->middleware('permission:create-user')->only('store');
        $this->middleware('permission:update-user')->only('update');
        $this->middleware('permission:delete-user')->only('destroy');
    }

    public function index(UserFilterRequest $request)
    {
        $filters = $request->validated();
        $users = $this->userService->list($filters);

        return self::paginated($users, 'users retrieved successfully');
    }

    public function store(UserStoreRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return self::success($user, 'user created successfully', 201);
    }

    public function show(int $id)
    {
        $user = $this->userService->findById($id);

        return self::success($user);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $user = $this->userService->update($user, $request->validated());

        return self::success($user, 'user updated successfully');
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return self::success(null, 'user deleted successfully');
    }
}