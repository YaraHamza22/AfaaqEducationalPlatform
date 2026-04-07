<?php

namespace Modules\ReportingModule\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Deprecated request kept only for backward compatibility.
 */
class GenerateDonorReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [];
    }
}
