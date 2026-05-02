<?php

namespace Modules\ReportingModule\Http\Requests\Report;

use App\Http\Requests\ApiFormRequest;

/**
 * Deprecated request kept only for backward compatibility.
 */
class GenerateDonorReportRequest extends ApiFormRequest
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
