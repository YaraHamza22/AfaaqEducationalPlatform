<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\User;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'=>'required|string|max:255',
            'email'=>'required|string',  //unique:users,email
            'password'=>['required','string','confirmed',
            Password::min(8)
                ->mixedCase()
                ->symbols()
                ->letters()
                ->numbers()
            ],
            'phone'=>'required|string|phone',
            'date_of_birth'=>'required|date',
            'gender'=>['required',Rule::in(['male','female'])],
            'address'=>'nullable|max:500',
            'role'=>['sometimes',Rule::exists('roles','name')]
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