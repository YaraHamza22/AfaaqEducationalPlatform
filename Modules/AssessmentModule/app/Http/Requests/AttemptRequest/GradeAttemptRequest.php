<?php

namespace Modules\AssessmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

class GradeAttemptRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
