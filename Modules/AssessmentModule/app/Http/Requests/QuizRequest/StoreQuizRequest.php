<?php

namespace Modules\AssessmentModule\Http\Requests\QuizRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
/**
 * Class StoreQuizRequest
 *
 * This class handles the validation rules for creating a new quiz. 
 * It defines the required and optional fields for the quiz, checks if the passing score is less than or equal to the maximum score,
 * and ensures that the passing score does not exceed 60% of the max score.
 * 
 * @package Modules\AssessmentModule\Http\Requests\QuizRequest
 */
class StoreQuizRequest extends FormRequest
{
        /**
     * Determine if the user is authorized to make this request.
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'exists:courses,course_id',
            ],

            'instructor_id' => [
                'required',
                'exists:users,id'
            ],

            'quizable_id' => [
                'nullable',
                'integer'
            ],

            'quizable_type' => [
                'nullable',
                'string',
                'max:255'
            ],

            'type' => [
                'required',
                'in:quiz,assignment,practice'
            ],

            'title' => [
                'required',
                'array'
            ],

            'title.*' => [
                'required',
                'string',
                'max:255'
            ],

            'description.*' => [
                'nullable',
                'string'
            ],

            'description' => [
                'nullable',
                'array'
            ],

            'max_score' => [
                'required',
                'integer',
                'min:1',
            ],

            'passing_score' => [
                'required',
                'integer',
                'min:0'
            ],

            'status' => [
                'required',
                'in:published,draft'
            ],

            'auto_grade_enabled' => [
                'required',
                'boolean'
            ],

            'available_from' => [
                'nullable',
                'date'
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:available_from'
            ],

            'duration_minutes' => [
                'nullable',
                'integer',
                'min:1'
            ],
        ];
    }

    /**
     * Custom validation logic after the default validation rules are applied.
     * 
     * This checks that the passing score is not greater than the maximum score 
     * and that the passing score does not exceed 60% of the max score.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $maxScore = (int) $this->input('max_score', 0);
            $passing = (int) $this->input('passing_score', 0);

            if ($passing > $maxScore) {
                $v->errors()->add('passing_score', 'Passing score cannot be greater than max score');
            }

            $limit = (int) floor($maxScore * 0.60);

            if ($passing > $limit) {
                $v->errors()->add('passing_score', "Passing score must be <= {$limit} (60% of max_score)");
            }
        });
    }

}
