<?php

namespace Modules\UserMangementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use \Modules\UserMangementModule\Services\V1\PermissionService;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:list-permissions')->only('index');
    }

    
    
    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        return self::success($permissions);
    }

}