<?php

namespace Modules\CommunicationModule\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class ReportForumPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,reviewed,rejected,resolved'],
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
