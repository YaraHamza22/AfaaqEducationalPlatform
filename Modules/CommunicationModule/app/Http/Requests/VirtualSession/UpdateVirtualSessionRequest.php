<?php

namespace Modules\CommunicationModule\Http\Requests\VirtualSession;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVirtualSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],
            'metadata' => ['sometimes', 'array'],
            'status' => ['sometimes', 'string', 'in:draft,published,cancelled,completed'],
            'join_url' => ['sometimes', 'nullable', 'url'],
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
