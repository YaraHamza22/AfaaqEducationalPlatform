<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionRequest\SuperAdmin;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Models\Question;

/**
 * UpdateQuestionRequest (Super Admin)
 */
class UpdateQuestionRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $question = $this->resolvedQuestion();
        $quizId = $this->input('quiz_id') ?? $question->quiz_id;

        return [
            'quiz_id'                    => ['sometimes', 'exists:quizzes,id'],
            'type'                       => ['sometimes', Rule::in(['mcq', 'true_false', 'text', 'multiple_choice', 'short_answer'])],
            'question_text'              => ['sometimes', 'array'],
            'question_text.*'            => ['sometimes', 'string', 'min:1'],
            'point'                      => ['sometimes', 'integer', 'min:1'],
            'order_index'                => [
                'sometimes', 'integer', 'min:1',
                Rule::unique('questions', 'order_index')
                    ->where(fn($q) => $q->where('quiz_id', $quizId))
                    ->ignore($question->id),
            ],
            'is_required'                => ['sometimes', 'boolean'],

            'options'                    => ['sometimes', 'array'],
            'options.*.id'               => ['sometimes', 'integer', 'exists:question_options,id'],
            'options.*.option_text'      => ['required_with:options', 'array'],
            'options.*.option_text.*'    => ['required_with:options', 'string', 'min:1'],
            'options.*.is_correct'       => ['required_with:options', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $question = $this->resolvedQuestion();
            $type    = strtolower(trim($this->input('type', $question->getRawOriginal('type'))));
            $options = $this->input('options');

            if ($options !== null) {
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
            }
        });
    }

    protected function resolvedQuestion(): Question
    {
        $param = $this->route('question');
        if ($param instanceof Question) {
            return $param;
        }
        return Question::query()->findOrFail((int) $param);
    }
}
