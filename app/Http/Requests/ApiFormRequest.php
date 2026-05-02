<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(
            response()->json([
                'message' => 'The submitted data is invalid. Please review the validation errors and try again.',
                'errors' => $errors,
            ], 422)
        );
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'required_without' => 'The :attribute field is required when :values is not provided.',
            'string' => 'The :attribute field must be a valid text value.',
            'numeric' => 'The :attribute field must be a valid number.',
            'integer' => 'The :attribute field must be a valid whole number.',
            'boolean' => 'The :attribute field must be true or false.',
            'array' => 'The :attribute field must be a valid list.',
            'email' => 'The :attribute field must be a valid email address.',
            'date' => 'The :attribute field must be a valid date.',
            'exists' => 'The selected :attribute is invalid or does not exist.',
            'unique' => 'The :attribute has already been taken.',
            'min' => 'The :attribute is below the allowed minimum value.',
            'max' => 'The :attribute exceeds the allowed maximum value.',
            'in' => 'The selected :attribute is invalid.',
            'mimes' => 'The :attribute file type is not supported.',
            'image' => 'The :attribute must be a valid image file.',
        ];
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach (array_keys($this->rules()) as $field) {
            $attributes[$field] = $this->humanizeAttribute($field);
        }

        return $attributes;
    }

    protected function humanizeAttribute(string $attribute): string
    {
        if (Str::endsWith($attribute, '.*')) {
            $base = Str::beforeLast($attribute, '.*');

            return Str::of($base)->replace('.', ' ')->replace('_', ' ')->lower()->toString() . ' item';
        }

        return Str::of($attribute)->replace('.', ' ')->replace('_', ' ')->lower()->toString();
    }
}
