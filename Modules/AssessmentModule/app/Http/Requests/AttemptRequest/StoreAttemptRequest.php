<?php

namespace Modules\AssessmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;
/**
 * Class StoreAttemptRequest
 *
 * This class handles the validation for creating a new attempt for a quiz. 
 * It ensures that the `quiz_id`, `student_id`, `attempt_number`, `score`, `is_passed`, `start_at`, and `ends_at` fields 
 * are valid according to the specified rules. 
 * The `attempt_number` must be unique for the specific student and quiz combination, 
 * while the `score` and `is_passed` fields are optional but validated if provided.
 * 
 * @package Modules\AssesmentModule\Http\Requests\AttemptRequest
 */
class StoreAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks if the user is authorized to create a new attempt for the specified quiz and student.
     * By default, it returns `true`, meaning the request is always authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return[
         'quiz_id' => ['required','integer','exists:quizzes,id'],
         'student_id' => ['required','integer','exists:users,id'],
         'attempt_number' => ['required','integer', 'min:1',
              Rule::unique('attempts','attempt_number')
              ->where(fn($q) => $q
              ->where('quiz_id', $this->input('quiz_id'))
              ->where('student_id',$this->input('student_id'))
              ),],
          'score' => ['sometimes','integer','min:0'],
          'is_passed' => ['sometimes','boolean'],
          'start_at' => ['sometimes', 'date'],
          'ends_at' => ['sometimes', 'date', 'after_or_equal:start_at'],  
        ];
    }
}
