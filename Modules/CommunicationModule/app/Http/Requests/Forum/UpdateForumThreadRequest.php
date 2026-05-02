<?php

namespace Modules\CommunicationModule\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class UpdateForumThreadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'is_pinned' => ['sometimes', 'boolean'],
            'is_locked' => ['sometimes', 'boolean'],
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
