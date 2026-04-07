<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelLang\Publisher\Console\Update;
use Modules\UserMangementModule\Http\Requests\Api\V1\Role\StoreRoleRequest;
use Modules\UserMangementModule\Http\Requests\Api\V1\Role\UpdateRoleRequest;
use Modules\UserMangementModule\Services\V1\RoleService;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    protected $roleService;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->middleware('permission:list-roles')->only('index');
        $this->middleware('permission:show-roles')->only('show');
        $this->middleware('permission:create-roles')->only('store');
        $this->middleware('permission:update-roles')->only('update');
        $this->middleware('permission:delete-roles')->only('destroy');
    }

    public function index(Request $request){
        return self::paginated(
            $this->roleService->getAllRoles($request->input('filters',[])),
            'Roles Retrived Successfully'
        );
    }



    public function store(StoreRoleRequest $request) {
        $role = $this->roleService->createRole($request->validated());
        return self::success($role, 'Role Created Successfully', 201);
    }

    public function show(Role $role)
    {
        $role = $this->roleService->getRole($role);
        return self::success($role, 'Role Retrived Successfully');  
    }


    public function update(UpdateRoleRequest $request, Role $role) 
    {
        $role = $this->roleService->updateRole($role, $request->validated());
        return self::success($role, 'Role Updated Successfully');
    }

    public function destroy(Role $role) {
        $this->roleService->deleteRole($role);
        return self::success(null, 'Role Deleted Successfully');
    }
}