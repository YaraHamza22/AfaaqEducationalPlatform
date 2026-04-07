<?php

namespace Modules\AssessmentModule\Http\Requests\QuestionRequest;

use Illuminate\Foundation\Http\FormRequest;
/**
 * Class StoreQuestionRequest
 * 
 * Request validation class for storing a new question in the system.
 * This class is responsible for validating the input data before saving a new question.
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuestionRequest
 */
class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @see FormRequest::authorize() 
     * @note Returns true by default, allowing all users to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * 
     * Merge the `quiz_id` from the route parameter into the request data.
     * This is useful if the `quiz_id` is passed through the route (e.g., `/quiz/{quiz}/questions`).
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        $quiz = $this->route('quiz');
        if($quiz instanceof Quiz) {
            $this->merge(['quiz_id' => $quiz->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * These rules define the expected structure of the request data.
     * The validation ensures that the request meets the required conditions before processing.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $quizId = $this->input('quiz_id');

        return [
            // Validate that the quiz_id exists in the quizzes table.
            'quiz_id' => ['required', 'exists:quizzes,id'],

            // Validate that the type is one of the allowed types.
            'type' => [
                'required',
                'in:mcq,true_false,text',
            ],

            // Validate that question_text is an array.
            'question_text' => [
                'required',
                'array',
            ],

            // Validate that each element of the question_text array is a string.
            'question_text.*' => [
                'required',
                'string',
            ],

            // Validate that the point is an integer and at least 1.
            'point' => [
                'required',
                'integer',
                'min:1',
            ],

            // Validate that order_index is unique within the quiz and greater than or equal to 1.
            'order_index' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('questions','order_index')
                    ->where(fn ($q) => $q->where('quiz_id', $quizId)),
            ],

            // Validate that is_required is a boolean value (true or false).
            'is_required' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation error messages for the request.
     * 
     * These messages will be returned to the client if validation fails.
     * The messages are localized in Arabic for the relevant fields.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع السؤال مطلوب',
            'type.in' => 'نوع السؤال غير صالح',

            'question_text.required' => 'نص السؤال مطلوب',
            'question_text.min' => 'نص السؤال قصير جدًا',

            'point.required' => 'علامة السؤال مطلوبة',
            'point.min' => 'علامة السؤال لا يمكن أن تكون سالبة',

            'order_index.required' => 'ترتيب السؤال مطلوب',
            'order_index.unique' => 'يوجد سؤال بنفس الترتيب داخل هذا الاختبار',

            'is_required.boolean' => 'قيمة is_required غير صحيحة',
        ];
    }
}