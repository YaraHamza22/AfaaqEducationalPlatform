<?php

namespace Modules\ReportingModule\Http\Requests\Snapshot;

use App\Http\Requests\ApiFormRequest;

class MaterializeSnapshotsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'snapshot_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
