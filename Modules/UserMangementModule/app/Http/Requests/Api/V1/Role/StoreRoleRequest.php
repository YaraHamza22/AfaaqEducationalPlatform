<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Role;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
                   
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $this->guard_name ?? 'api'),
            ],
            
            'guard_name' => 'nullable|string|in:api,web',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}