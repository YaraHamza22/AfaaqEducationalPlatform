<?php

namespace Modules\CommunicationModule\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class ReactForumPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reaction' => ['required', 'string', 'max:30'],
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
