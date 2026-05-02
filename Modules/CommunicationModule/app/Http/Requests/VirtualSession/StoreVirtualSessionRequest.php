<?php

namespace Modules\CommunicationModule\Http\Requests\VirtualSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\CommunicationModule\Models\ExternalIntegration;

class StoreVirtualSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'integration_id' => ['required', 'integer', 'exists:external_integrations,id'],
            'provider' => ['required', 'string', 'in:zoom,google_classroom'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $integration = ExternalIntegration::query()->find($this->input('integration_id'));
            if (! $integration || (int) $integration->user_id !== (int) Auth::id()) {
                $validator->errors()->add('integration_id', 'Integration must belong to the authenticated user.');
                return;
            }

            if ((string) $integration->provider !== (string) $this->input('provider')) {
                $validator->errors()->add('provider', 'Provider must match selected integration.');
            }
        });
    }
}
