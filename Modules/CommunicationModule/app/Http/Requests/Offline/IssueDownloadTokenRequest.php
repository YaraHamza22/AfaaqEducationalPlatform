<?php

namespace Modules\CommunicationModule\Http\Requests\Offline;

use Illuminate\Foundation\Http\FormRequest;

class IssueDownloadTokenRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'device_id' => ['nullable', 'string', 'max:120'],
            'expires_at' => ['required', 'date', 'after:now'],
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
