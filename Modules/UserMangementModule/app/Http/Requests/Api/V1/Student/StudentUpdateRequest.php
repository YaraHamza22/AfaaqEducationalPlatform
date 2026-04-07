<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentUpdateRequest extends FormRequest
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
            'address'=>'sometimes|nullable|max:500',
            'education_level'=>'sometimes|string',
            'country'=>'sometimes|string',
            'bio' => 'sometimes|nullable|text|max:1000',
            'specialization' => 'sometimes|nullable|string|max:255',
            'joined_at' => 'sometimes|nullable|date'
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