<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Instructor;

use App\Http\Requests\ApiFormRequest;

class InstructorFilterRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'term'=>'soetimes|string|max:100',
            'years'=>'sometimes|int',
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