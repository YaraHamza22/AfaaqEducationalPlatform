<?php
namespace Modules\UserMangementModule\Services\V1;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class PermissionService
{

    public function getAllPermissions($perPage = 15){
            try{
                return Permission::query()
                        ->select(['id','name'])
                        ->latest('id')
                        ->paginate($perPage);

            }catch(\Exception $e){
                Log::error('get all permissions failed:'. $e->getMessage(),[
                    'exception' => $e,
                ]);
                throw new HttpResponseException(response()->json([
                        'status' => 'error',
                        'message' => 'failed to update role'
                    ], 500));
            }
        }
}