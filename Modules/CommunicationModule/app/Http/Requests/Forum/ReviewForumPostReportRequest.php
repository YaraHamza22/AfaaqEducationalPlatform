<?php

namespace Modules\CommunicationModule\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class ReviewForumPostReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:reviewed,rejected,resolved'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
