<?php

namespace Modules\CommunicationModule\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatThreadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['integer', 'exists:users,id'],
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
