<?php

namespace Modules\UserMangementModule\Services\V1;

use DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Log;
use Spatie\Permission\Models\Role;

class RoleService
{
    
    public function getAllRoles(array $filters = [])
    {
        try{
            return Role::with('permissions:id,name')
            ->select('id','name')
            ->latest('id')
            ->paginate();
        }
        catch(\Exception $e){
            Log::error('get all failed roles:'. $e->getMessage(),[
                'exception' => $e,
            ]);
            throw new HttpResponseException(response()->json([
                    'status' => 'error',
                    'message' => 'failed to get all roles'
                ], 500));
        }        
    }  

    public function getRole(Role $role)
    {
        return $role->load('permissions:id,name,guard_name');
    }

    public function createRole(array $data)
    {
       return DB::transaction(function() use ($data) {
            try{
                $permissions = $data['permissions'] ?? [];
                $roleData = collect($data)->except(['permissions'])->all();
                $role = Role::create($roleData);
                if(!empty($permissions)){
                    $role->givePermissionTo($permissions);
                }

                return $role->load('permissions:id,name,guard_name');

            }catch(\Exception $e){

                Log::error('create role failed:'. $e->getMessage(),[
                    'exception' => $e,
                    'data' => $data
                ]);
                throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => 'failed to create role'
                    ], 500));
            }
        });

    }

    public function updateRole(Role $role, array $data)
    {
        return DB::transaction(function() use ($role, $data) { 
            try{
                $permissions = $data['permissions'] ?? [];
                $roleData = collect($data)->except(['permissions'])->all();
                $role->update($roleData);
                if(!empty($permissions)){
                    $role->syncPermissions($permissions);
                }

                DB::commit();
                return $role->load('permissions:id,name,guard_name');
            }catch(\Exception $e){
                Log::error('update role failed:'. $e->getMessage(),[
                    'exception' => $e,
                    'data' => $data,
                    'role_id' => $role->id
                ]);
              
               throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => 'failed to update role'
                    ], 500));
            }
        });


    }

    public function deleteRole(Role $role)
    {
        try{
            $role->delete();
        }catch(\Exception $e){
                Log::error('update role failed:'. $e->getMessage(),[
                'exception' => $e,
                'role_id' => $role->id
            ]);
              throw new HttpResponseException(response()->json([
                      'status' => 'error',
                      'message' => 'failed to delete role'
                 ], 500));
        }
    }
}