<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\User;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'roles'=>'sometimes|array|min:1',
            'roles.*'=>'string|exists:roles,name',
            'term'=>'sometimes|string|max:100',
            'gender'=>['sometimes','string',Rule::in(['male','female'])],
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