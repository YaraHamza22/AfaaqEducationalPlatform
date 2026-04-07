<?php

namespace Modules\AssessmentModule\Http\Requests\QuestionRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateQuestionRequest
 *
 * This class is responsible for validating the request data when updating an existing question for a quiz.
 * It includes validation rules for fields such as `type`, `question_text`, `point`, `order_index`, and `is_required`.
 * The class ensures that the `order_index` is unique within the same quiz but ignores the current question being updated.
 * The `quiz_id` is automatically merged into the request data based on the quiz route parameter.
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuestionRequest
 */
class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user is authorized to update the question for the specified quiz.
     * By default, it returns true, meaning the request is always authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method automatically merges the `quiz_id` into the request data based on the quiz route parameter.
     * It ensures that the request contains the correct `quiz_id` for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $quiz = $this->route('quiz');
        if ($quiz instanceof Quiz) {
            $this->merge(['quiz_id' => $quiz->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The method returns an array of validation rules, with conditional checks for optional fields (using the 'sometimes' rule).
     * It includes rules for required fields, field types, and uniqueness checks, particularly for `order_index`, ensuring it remains unique within the quiz.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        $question = $this->route('question');
        $quizId = (int) ($this->input('quiz_id') ?? $question->quiz_id);

        return [
            'type' => [
                'sometimes', 
                'in:mcq,true_false,text',
            ],

            'question_text' => [
                'sometimes',
                'array',
            ],
            'question_text.*' => [
                'sometimes', 
                'string',
            ],

            'point' => [
                'sometimes', 
                'integer',
                'min:1',
            ],

            'order_index' => [
                'sometimes', 
                'integer',
                'min:1',
                Rule::unique('questions', 'order_index')
                    ->where(fn($q) => $q->where('quiz_id', $quizId))
                    ->ignore($question->id) // Ignores the current question being updated
            ],

            'is_required' => [
                'sometimes', // Allows partial updates
                'boolean',
            ],
        ];
    }
}