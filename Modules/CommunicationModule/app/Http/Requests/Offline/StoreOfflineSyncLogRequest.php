<?php

namespace Modules\CommunicationModule\Http\Requests\Offline;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfflineSyncLogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'offline_package_id' => ['nullable', 'integer', 'exists:offline_packages,id'],
            'device_id' => ['required', 'string', 'max:120'],
            'action' => ['required', 'string', 'max:60'],
            'payload' => ['nullable', 'array'],
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
