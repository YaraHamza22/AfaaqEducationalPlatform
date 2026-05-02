<?php

namespace Modules\AssesmentModule\Http\Requests\QuizRequest;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\ApiFormRequest;

/**
 * Class StoreQuizRequest
 *
 * This class handles the validation rules for creating a new quiz. 
 * It defines the required and optional fields for the quiz, checks if the passing score is less than or equal to the maximum score,
 * and ensures that the passing score does not exceed 60% of the max score.
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuizRequest
 */
class StoreQuizRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [

            'instructor_id' => [
                'required',
                'exists:users,id'
            ],

            'quizable_id' => [
                'required',
                'integer',  
            ],

            'quizable_type' => [
               'required',
                new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\QuizType::class)
            ],

            'type' => [
                'required',
                new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\AssesmentType::class)
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
                new \Illuminate\Validation\Rules\Enum(\Modules\AssesmentModule\Enums\QuizStatus::class)
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
