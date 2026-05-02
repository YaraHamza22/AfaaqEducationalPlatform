<?php

namespace Modules\UserMangementModule\Http\Requests\Api\V1\Student;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Modules\UserMangementModule\Enums\EducationalLevel;

class StudentFilterRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'term'=>'sometimes|string|max:100',
            'levels'=>['sometimes','array','min:1'],
            'levels.*' => [new Enum(EducationalLevel::class)]
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