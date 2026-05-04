<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreQuestionRequest (Super Admin)
 *
 * Validates a single question creation request.
 * Enforces per-type options rules:
 *  - mcq / multiple_choice → at least 2 options, exactly 1 correct
 *  - true_false             → exactly 2 options, exactly 1 correct
 *  - text / short_answer    → no options required
 */
class StoreQuestionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $quizId = $this->input('quiz_id');

        return [
            'quiz_id'                    => ['required', 'exists:quizzes,id'],
            'type'                       => ['required', Rule::in(['mcq', 'true_false', 'text', 'multiple_choice', 'short_answer'])],
            'question_text'              => ['required', 'array'],
            'question_text.*'            => ['required', 'string', 'min:1'],
            'point'                      => ['required', 'integer', 'min:1'],
            'order_index'                => [
                'required', 'integer', 'min:1',
                Rule::unique('questions', 'order_index')
                    ->where(fn($q) => $q->where('quiz_id', $quizId)),
            ],
            'is_required'                => ['required', 'boolean'],

            // Options (conditionally required by withValidator)
            'options'                    => ['sometimes', 'array'],
            'options.*.option_text'      => ['required_with:options', 'array'],
            'options.*.option_text.*'    => ['required_with:options', 'string', 'min:1'],
            'options.*.is_correct'       => ['required_with:options', 'boolean'],
        ];
    }

    /**
     * Apply per-type cross-field validation after standard rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $type    = strtolower(trim($this->input('type', '')));
            $options = $this->input('options', []);

            if (in_array($type, ['mcq', 'multiple_choice'])) {
                if (count($options) < 2) {
                    $v->errors()->add('options', 'MCQ questions require at least 2 options.');
                } else {
                    $correct = count(array_filter($options, fn($o) => (bool) ($o['is_correct'] ?? false)));
                    if ($correct !== 1) {
                        $v->errors()->add('options', 'MCQ questions must have exactly one correct option.');
                    }
                }
            }

            if ($type === 'true_false') {
                if (count($options) !== 2) {
                    $v->errors()->add('options', 'True/False questions must have exactly 2 options.');
                } else {
                    $correct = count(array_filter($options, fn($o) => (bool) ($o['is_correct'] ?? false)));
                    if ($correct !== 1) {
                        $v->errors()->add('options', 'True/False questions must have exactly one correct option.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'quiz_id.required'           => 'Quiz ID is required.',
            'quiz_id.exists'             => 'The specified quiz does not exist.',
            'type.required'              => 'Question type is required.',
            'type.in'                    => 'Invalid type. Allowed: mcq, true_false, text, multiple_choice, short_answer.',
            'question_text.required'     => 'Question text is required.',
            'point.required'             => 'Points value is required.',
            'point.min'                  => 'Points must be at least 1.',
            'order_index.required'       => 'Order index is required.',
            'order_index.unique'         => 'A question with this order already exists in this quiz.',
            'is_required.required'       => 'The is_required field is required.',
            'is_required.boolean'        => 'The is_required field must be true or false.',
        ];
    }
}
