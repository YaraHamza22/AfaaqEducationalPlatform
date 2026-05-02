<?php

namespace Modules\AssesmentModule\Http\Requests\AnswerRequest;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Enums\QuestionType;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Models\Question;
use Modules\AssesmentModule\Models\QuestionOption;

/**
 * Validates partial updates to an answer (same optional-field style as UpdateQuestionRequest).
 *
 * @package Modules\AssesmentModule\Http\Requests\AnswerRequest
 */
class UpdateAnswerRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'answer_text' => ['nullable', 'array'],
            'answer_text.*' => ['nullable', 'string'],
            'boolean_answer' => ['nullable', 'boolean'],
            'is_correct' => ['sometimes', 'boolean'],
            'question_score' => ['sometimes', 'integer', 'min:0'],
            'graded_at' => ['sometimes', 'date'],
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $answer = $this->resolvedAnswer();
            $question = Question::query()->find($answer->question_id);
            if (! $question) {
                return;
            }

            $type = $question->type;

            if ($this->has('selected_option_id')) {
                $selected = $this->input('selected_option_id');
                if ($selected !== null) {
                    if ($type !== QuestionType::MULTIPLE_CHOICE) {
                        $v->errors()->add('selected_option_id', 'selected_option_id is only valid for multiple choice questions.');
                    } else {
                        $belongs = QuestionOption::query()
                            ->where('id', (int) $selected)
                            ->where('question_id', $question->id)
                            ->exists();
                        if (! $belongs) {
                            $v->errors()->add('selected_option_id', 'هذا الخيار لا يتبع لهذا السؤال.');
                        }
                    }
                }
            }

            if ($this->has('boolean_answer') && $this->input('boolean_answer') !== null) {
                if ($type !== QuestionType::TRUE_FALSE) {
                    $v->errors()->add('boolean_answer', 'boolean_answer is only valid for true/false questions.');
                }
            }

            if ($this->has('answer_text')) {
                $text = $this->input('answer_text');
                $hasText = is_array($text)
                    && collect($text)->filter(fn ($x) => is_string($x) && trim($x) !== '')->isNotEmpty();

                if ($type === QuestionType::SHORT_ANSWER && ! $hasText) {
                    $v->errors()->add('answer_text', 'answer_text cannot be empty for short answer questions.');
                }

                if ($type !== QuestionType::SHORT_ANSWER && $hasText) {
                    $v->errors()->add('answer_text', 'answer_text is only valid for short answer questions.');
                }
            }
        });
    }

    /**
     * Resolve the answer from route model binding or raw {answer} id (matches UpdateQuestionRequest pattern).
     */
    protected function resolvedAnswer(): Answer
    {
        $param = $this->route('answer');

        if ($param instanceof Answer) {
            return $param;
        }

        return Answer::query()->findOrFail((int) $param);
    }
}
