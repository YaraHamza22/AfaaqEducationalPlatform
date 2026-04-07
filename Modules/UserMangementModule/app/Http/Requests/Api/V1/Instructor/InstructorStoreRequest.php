<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InstructorStoreRequest extends FormRequest
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
            'specialization'=>'required|string|max:255',
            'bio'=>'nullable|string|max:1500',
            'years_of_experience'=>'required|integer',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'cv'=> 'nullable|mimes:pdf|max:5120',
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