<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionOptionRequest;

use Modules\AssesmentModule\Models\Question;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Enums\QuestionType;
/**
 * Class StoreQuestionOptionRequest
 *
 * This class handles the validation of the request data when creating new options for a question.
 * It validates fields such as `question_id`, `option_text`, and `is_correct`. Additionally, it ensures that the options 
 * for a question are unique and belong to the correct type of question (MCQ).
 * The `question_id` is automatically merged into the request from the route parameters.
 * 
 * @package Modules\AssesmentModule\Http\Requests\QuestionOptionRequest
 */
class StoreQuestionOptionRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user is authorized to add options to the specified question.
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
     * This method automatically merges the `question_id` into the request data based on the question route parameter.
     * It ensures that the request contains the correct `question_id` for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $question = $this->route('question');
        if ($question instanceof Question) {
            $this->merge(['question_id' => $question->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method returns an array of validation rules, including checks for required fields, uniqueness of options, 
     * and whether the option belongs to a valid question.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $qid = $this->input('question_id');
        return [
            'question_id' => ['required', 'exists:questions,id'],
            'option_text' => ['required', 'array'],
            'option_text.*' => [
                'required',
                'string',
                Rule::unique('question_options', 'option_text')
                    ->where(fn($q) => $q->where('question_id', $this->input('question_id'))),
            ],
            'is_correct' => ['required', 'boolean'],
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * This method returns an array of custom error messages in Arabic, making it easier for the user 
     * to understand validation issues.
     *
     * @return array
     */
    public function message(): array
    {
        return [
            'question_id.required' => 'السؤال مطلوب',
            'question.exists' => 'السؤال غير موجود',
            'question.required' => 'نص الخيار مطلوب',
            'option_text.unique' => 'هذا الخيار موجود مسبقا لنفس السؤال',
            'is_correct.required' => 'يرجى تحديد ان كان الخيار صحيحا',
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
        $validator->after(function (Validator $v) {
            $question = $this->route('question_id');
            if (!($question instanceof Question)) {
                $qid = $this->input('question_id');
                $question = $qid ? Question::query()->find($qid) : null;
            }
            if (!$question) return;

            if ($question->type !== QuestionType::MULTIPLE_CHOICE) {
                $v->errors()->add('question_id', 'Options are allowed only for MCQ questions.');
            }
        });
    }
}
