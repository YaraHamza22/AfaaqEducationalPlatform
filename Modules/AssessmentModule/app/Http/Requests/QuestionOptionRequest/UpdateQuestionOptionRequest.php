<?php

namespace Modules\AssessmentModule\Http\Requests\QuestionOptionRequest;

use Illuminate\Foundation\Http\FormRequest;

 /**
 * Class UpdateQuestionOptionRequest
 *
 * This class is responsible for validating the request data when updating an existing option for a question.
 * It validates fields such as `option_text` and `is_correct`. Additionally, it ensures that the updated option text 
 * is unique within the same question, while ignoring the current option being updated. 
 * It also ensures that options can only be updated for questions of type MCQ (Multiple Choice Question).
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuestionOptionRequest
 */
class UpdateQuestionOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user is authorized to update the option for the specified question.
     * By default, it returns true, meaning the request is always authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method returns an array of validation rules for the fields `option_text`, `is_correct`, and `question_id`. 
     * It ensures that the option text is unique within the same question, ignoring the current option being updated.
     * It also validates that the option's `is_correct` field is a boolean.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $option = $this->route('question_option');
        $qid = $this->input('question_id') ?? optional($option)->question_id;

        return [
            'option_text.*' => [
                'sometimes', // Optional when updating
                'string',
                Rule::unique('question_options', 'option_text')
                    ->where(fn($q) => $q->where('question_id', $qid))
                    ->ignore(optional($option)->id), // Ignore the current option being updated
            ],
            'option_text' => [
                'sometimes', // Optional when updating
                'array',
            ],
            'is_correct' => [
                'sometimes', // Optional when updating
                'boolean',
            ],
        ];
    }

    /**
     * Custom validation logic after the default validation rules are applied.
     *
     * This method ensures that options are only allowed for MCQ (Multiple Choice Questions). 
     * If the question type is not 'mcq', it adds a custom validation error to the request.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator1) {
            $question = $this->route('question');
            if ($question instanceof Question && $question->type !== 'mcq') {
                $validator1->errors()->add('question_id', 'Options are allowed only for MCQ questions.');
            }
        });
    }
}
