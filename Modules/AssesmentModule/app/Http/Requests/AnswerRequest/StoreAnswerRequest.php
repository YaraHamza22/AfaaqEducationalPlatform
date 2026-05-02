<?php

namespace Modules\AssesmentModule\Http\Requests\AnswerRequest;

use Modules\AssesmentModule\Enums\AttemptStatus;
use Modules\AssesmentModule\Enums\QuestionType;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Question;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Models\QuestionOption;

/**
 * Class StoreAnswerRequest
 *
 * This class handles the validation logic for storing answers. It validates the input data
 * based on various rules such as required fields, correct foreign key references, and the
 * constraints of the specific question type (MCQ, True/False, Text).
 *
 * It also includes custom validation logic to ensure that:
 * - The attempt is in progress
 * - The question belongs to the correct quiz
 * - The user cannot submit multiple answers for the same question in the same attempt
 * - The input is valid for the specific question type (e.g., selected option for MCQ, boolean answer for true/false, etc.)
 *
 * @package Modules\AssesmentModule\Http\Requests\AnswerRequest
 */
class StoreAnswerRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * In this case, authorization is always granted.
     *
     * @return bool Always true, as we assume the user is authorized to submit answers.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method returns an array of validation rules for the incoming request data.
     * The rules vary based on the fields being submitted and include checks for valid foreign keys,
     * correct answer types, and required fields.
     *
     * @return array<string, mixed> The validation rules for the answer submission.
     */
    public function rules(): array
    {
        return [
            'attempt_id' => ['required', 'integer', 'exists:attempts,id'],

            // The question_id must be required, an integer, and must exist in the questions table.
            'question_id' => ['required', 'integer', 'exists:questions,id'],

            // The selected_option_id is optional, but if provided, must be a valid integer that exists in the question_options table.
            'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],

            // answer_text is optional but must be an array if provided, and each element should be a string.
            'answer_text' => ['nullable', 'array'],
            'answer_text.*' => ['nullable', 'string'],

            // boolean_answer is optional but must be a boolean if provided.
            'boolean_answer' => ['nullable', 'boolean'],

            // is_correct and question_score are optional, but if provided, must be a boolean and an integer respectively.
            'is_correct' => ['sometimes', 'boolean'],
            'question_score' => ['sometimes', 'integer', 'min:0'],

            // graded_at and graded_by are optional, but if provided, must be a valid date and an existing user ID respectively.
            'graded_at' => ['sometimes', 'date'],
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get custom error messages for the validation rules.
     *
     * This method customizes the validation error messages for various fields to provide more
     * user-friendly messages.
     *
     * @return array<string, string> Custom validation messages for the answer submission.
     */
    public function messages(): array
    {
        return [
            'attempt_id.required' => 'المحاولة مطلوبة',
            'attempt_id.exists'   => 'المحاولة غير موجودة',

            'question_id.required' => 'السؤال مطلوب',
            'question_id.exists'   => 'السؤال غير موجود',

            'selected_option_id.exists' => 'الخيار المحدد غير صحيح',
            'boolean_answer.boolean'    => 'اجابة النعم/لا غير صحيحة',
        ];
    }

    /**
     * Define additional validation logic after the initial validation.
     *
     * This method checks conditions that depend on other fields or complex relationships, such as:
     * - Ensuring the attempt is still in progress before submitting an answer.
     * - Validating that the question belongs to the correct quiz.
     * - Ensuring the same question is not answered multiple times within the same attempt.
     * - Validating that the correct input is provided based on the question type (MCQ, true/false, text).
     *
     * @param Validator $validator The validator instance.
     * 
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $attemptId  = $this->input('attempt_id');
            $questionId = $this->input('question_id');

            // If attempt_id or question_id is missing, return early.
            if (!$attemptId || !$questionId) {
                return;
            }

            // Retrieve the attempt record and ensure it is in progress.
            $attempt = Attempt::query()->find($attemptId);
            if (!$attempt) return;

            if ($attempt->status !== AttemptStatus::IN_PROGRESS) {
                $v->errors()->add('attempt_id', 'لا يمكن إرسال إجابة لأن حالة المحاولة ليست In Progress.');
                return;
            }

            // Retrieve the question record and check if it belongs to the correct quiz.
            $question = Question::query()->find($questionId);
            if (!$question) return;

            if (!empty($attempt->quiz_id) && !empty($question->quiz_id) && (int)$attempt->quiz_id !== (int)$question->quiz_id) {
                $v->errors()->add('question_id', 'هذا السؤال لا يتبع لنفس الاختبار (Quiz).');
                return;
            }

            // Ensure the same question is not answered multiple times within the same attempt.
            $already = Answer::query()
                ->where('attempt_id', (int)$attemptId)
                ->where('question_id', (int)$questionId)
                ->exists();

            if ($already) {
                $v->errors()->add('question_id', 'لا يمكنك إرسال إجابتين لنفس السؤال ضمن نفس المحاولة');
                return;
            }

            // Validate the specific answer type based on the question type.
            $type     = $question->type;
            $selected = $this->input('selected_option_id');
            $text     = $this->input('answer_text');
            $bool     = $this->input('boolean_answer');

            // For multiple-choice questions, ensure a valid option is selected.
            if ($type === QuestionType::MULTIPLE_CHOICE) {
                if ($selected === null) {
                    $v->errors()->add('selected_option_id', 'selected_option_id is required for multiple choice questions.');
                    return;
                }

                $belongs = QuestionOption::query()
                    ->where('id', (int)$selected)
                    ->where('question_id', (int)$questionId)
                    ->exists();

                if (!$belongs) {
                    $v->errors()->add('selected_option_id', 'هذا الخيار لا يتبع لهذا السؤال.');
                    return;
                }
            }

            // For True/False questions, ensure a boolean answer is provided.
            if ($type === QuestionType::TRUE_FALSE) {
                if ($bool === null) {
                    $v->errors()->add('boolean_answer', 'boolean_answer is required for true_false questions.');
                    return;
                }
            }

            // For short answer questions, ensure there is non-empty text.
            if ($type === QuestionType::SHORT_ANSWER) {
                $hasText = is_array($text) && collect($text)->filter(fn ($x) => is_string($x) && trim($x) !== '')->isNotEmpty();
                if (!$hasText) {
                    $v->errors()->add('answer_text', 'answer_text is required for short answer questions.');
                    return;
                }
            }
        });
    }
}
