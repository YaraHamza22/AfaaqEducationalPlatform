<?php

namespace Modules\AssesmentModule\Http\Requests\QuizRequest;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ApiFormRequest;

/**
 * Class UpdateQuizRequest
 *
 * This class handles the validation rules for updating an existing quiz. 
 * It allows for partial updates using the 'sometimes' rule, meaning only the 
 * fields provided in the request will be validated. Additionally, custom validation 
 * ensures that the passing score is not greater than the maximum score, 
 * and that it is within 60% of the maximum score.
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuizRequest
 */
class UpdateQuizRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * This method returns an array of validation rules, with conditional checks 
     * for optional fields (using the 'sometimes' rule) that will only be validated if 
     * they are included in the request. 
     * 
     * @return array
     */
    public function rules(): array
    {
        return [
            'instructor_id' => [
                'sometimes', // Allows partial updates
                'exists:users,id'
            ],

            'quizable_id' => [
                'nullable',
                'integer'
            ],

            'quizable_type' => [
                'nullable',
                'string',
               new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\QuizType::class)
            ],

            'type' => [
                'sometimes', // Allows partial updates
                new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\AssesmentType::class)
            ],

            'title' => [
                'sometimes', // Allows partial updates
                'array'
            ],

            'title.*' => [
                'sometimes', // Allows partial updates
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
                'sometimes', // Allows partial updates
                'integer',
                'min:1',
            ],

            'passing_score' => [
                'sometimes', // Allows partial updates
                'integer',
                'min:0'
            ],

            'status' => [
                'sometimes', // Allows partial updates
                new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\QuizStatus::class)
            ],

            'auto_grade_enabled' => [
                'sometimes', // Allows partial updates
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

        /*
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
            return;
        }

        $limit = (int) floor($maxScore * 0.60);

        if ($passing < $limit) {
            $v->errors()->add('passing_score', "Passing score must be >= {$limit} (60% of max_score)");
        }
    });
}
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
