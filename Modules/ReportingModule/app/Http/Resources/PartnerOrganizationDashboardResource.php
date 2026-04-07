<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerOrganizationDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'deprecated' => $this->resource['deprecated'] ?? true,
            'message' => $this->resource['message'] ?? 'Organization reporting was removed.',
        ];
    }
}
