<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use App\Http\Requests\ApiFormRequest;

class SubmitAttemptRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize payload when:
     * - Postman sends raw body as "Text" (no JSON Content-Type), so Laravel does not fill inputs.
     * - answers is sent as a JSON-encoded string.
     */
    protected function prepareForValidation(): void
    {
        $answers = $this->input('answers');

        if ($answers !== null && ! is_string($answers)) {
            return;
        }

        if (is_string($answers)) {
            $decoded = json_decode($answers, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['answers' => $decoded]);
            }

            return;
        }

        $content = trim((string) $this->getContent());
        if ($content === '') {
            return;
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return;
        }

        $this->merge($decoded);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'answers.*.answer_text' => ['nullable', 'array'],
            'answers.*.answer_text.*' => ['nullable', 'string'],
            'answers.*.boolean_answer' => ['nullable', 'boolean'],
        ];
    }
}
