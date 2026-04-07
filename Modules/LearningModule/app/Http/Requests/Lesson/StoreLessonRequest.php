<?php

namespace Modules\LearningModule\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new lesson.
 * Translatable fields (title, description) accept string or array with en/ar keys.
 */
class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'description'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $this->merge([$key => ['en' => $this->input($key)]]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', 'exists:units,unit_id'],
            'title' => ['required', 'array'],
            'title.en' => ['required_without:title.ar', 'nullable', 'string', 'max:255'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'lesson_order' => ['nullable', 'integer', 'min:1'],
            'lesson_type' => ['required', 'string', Rule::in(['lecture', 'video', 'interactive', 'reading'])],
            'is_required' => ['nullable', 'boolean'],
            'actual_duration_minutes' => ['required', 'integer', 'min:1'],
            'video'=> 'nullable|file|mimes:mp4,mov,ogg,qt|max:51200',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,zip,rar,doc,docx,ppt,pptx|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_id.required' => 'The unit is required.',
            'unit_id.exists' => 'The selected unit does not exist.',
            'title.required' => 'The lesson title is required.',
            'title.max' => 'The lesson title may not be greater than 255 characters.',
            'lesson_order.min' => 'Lesson order must be at least 1.',
            'lesson_type.required' => 'The lesson type is required.',
            'lesson_type.in' => 'Please select a valid lesson type (lecture, video, interactive, reading).',
            'actual_duration_minutes.required' => 'Actual duration is required.',
            'actual_duration_minutes.min' => 'Actual duration must be at least 1 minute.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'unit_id' => 'unit',
            'lesson_order' => 'lesson order',
            'lesson_type' => 'lesson type',
            'is_required' => 'required',
            'actual_duration_minutes' => 'actual duration',
        ];
    }
}
