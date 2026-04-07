<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'=>'required|string|max:255',
            'email'=>'required|string',
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
            'education_level'=>'required|string',
            'country'=>'required|string',
            'bio' => 'nullable|text|max:1000',
            'specialization' => 'nullable|string|max:255',
            'joined_at' => 'nullable|date'
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