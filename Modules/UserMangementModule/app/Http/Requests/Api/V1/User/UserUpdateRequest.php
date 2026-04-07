<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'=>'sometimes|string|max:255',
            'email'=>'sometimes|string',  //unique:users,email
            'password'=>['sometimes','string','confirmed',
            Password::min(8)
                ->mixedCase()
                ->symbols()
                ->letters()
                ->numbers()
            ],
            'phone'=>'sometimes|string|phone',
            'date_of_birth'=>'sometimes|date',
            'gender'=>['sometimes',Rule::in(['male','female'])],
            'address'=>'sometimes|max:500'
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