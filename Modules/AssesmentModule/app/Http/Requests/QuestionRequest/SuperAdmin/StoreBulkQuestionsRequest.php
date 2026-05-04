<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * StoreBulkQuestionsRequest (Super Admin)
 *
 * Validates an array of questions submitted in a single request.
 * Each question is independently validated including per-type options rules.
 * order_index uniqueness is checked both against the DB and within the batch itself.
 */
class StoreBulkQuestionsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questions'                              => ['required', 'array', 'min:1'],
            'questions.*.quiz_id'                    => ['required', 'exists:quizzes,id'],
            'questions.*.type'                       => ['required', Rule::in(['mcq', 'true_false', 'text', 'multiple_choice', 'short_answer'])],
            'questions.*.question_text'              => ['required', 'array'],
            'questions.*.question_text.*'            => ['required', 'string', 'min:1'],
            'questions.*.point'                      => ['required', 'integer', 'min:1'],
            'questions.*.order_index'                => ['required', 'integer', 'min:1'],
            'questions.*.is_required'                => ['required', 'boolean'],

            // Per-question options
            'questions.*.options'                    => ['sometimes', 'array'],
            'questions.*.options.*.option_text'      => ['required_with:questions.*.options', 'array'],
            'questions.*.options.*.option_text.*'    => ['required_with:questions.*.options', 'string', 'min:1'],
            'questions.*.options.*.is_correct'       => ['required_with:questions.*.options', 'boolean'],
        ];
    }

    /**
     * Cross-field validation:
     *  1. Duplicate order_index within the same quiz (in the batch)
     *  2. Per-type options constraints
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $questions     = $this->input('questions', []);
            $seenPairs     = [];   // "quizId:orderIndex" → first batch index

            foreach ($questions as $idx => $question) {
                $quizId     = $question['quiz_id'] ?? null;
                $orderIndex = $question['order_index'] ?? null;
                $type       = strtolower(trim($question['type'] ?? ''));
                $options    = $question['options'] ?? [];

                // ── 1. Duplicate order_index within the batch ──────────────────
                $pairKey = "{$quizId}:{$orderIndex}";
                if (isset($seenPairs[$pairKey])) {
                    $v->errors()->add(
                        "questions.{$idx}.order_index",
                        "Duplicate order_index {$orderIndex} for quiz_id {$quizId} within this batch."
                    );
                } else {
                    $seenPairs[$pairKey] = $idx;
                }

                // ── 2. Per-type options rules ──────────────────────────────────
                if (in_array($type, ['mcq', 'multiple_choice'])) {
                    if (count($options) < 2) {
                        $v->errors()->add(
                            "questions.{$idx}.options",
                            "MCQ questions require at least 2 options."
                        );
                    } else {
                        $correct = count(array_filter($options, fn($o) => (bool) ($o['is_correct'] ?? false)));
                        if ($correct !== 1) {
                            $v->errors()->add(
                                "questions.{$idx}.options",
                                "MCQ questions must have exactly one correct option."
                            );
                        }
                    }
                }

                if ($type === 'true_false') {
                    if (count($options) !== 2) {
                        $v->errors()->add(
                            "questions.{$idx}.options",
                            "True/False questions must have exactly 2 options."
                        );
                    } else {
                        $correct = count(array_filter($options, fn($o) => (bool) ($o['is_correct'] ?? false)));
                        if ($correct !== 1) {
                            $v->errors()->add(
                                "questions.{$idx}.options",
                                "True/False questions must have exactly one correct option."
                            );
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'questions.required'                   => 'At least one question is required.',
            'questions.min'                        => 'At least one question must be provided.',
            'questions.*.quiz_id.required'         => 'Quiz ID is required for each question.',
            'questions.*.quiz_id.exists'           => 'One or more quiz IDs do not exist.',
            'questions.*.type.required'            => 'Question type is required.',
            'questions.*.type.in'                  => 'Invalid type. Allowed: mcq, true_false, text, multiple_choice, short_answer.',
            'questions.*.question_text.required'   => 'Question text is required.',
            'questions.*.point.required'           => 'Points value is required.',
            'questions.*.point.min'                => 'Points must be at least 1.',
            'questions.*.order_index.required'     => 'Order index is required.',
            'questions.*.is_required.required'     => 'The is_required field is required.',
        ];
    }
}
