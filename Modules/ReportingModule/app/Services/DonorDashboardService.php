<?php

namespace Modules\ReportingModule\Services;

/**
 * Deprecated service kept only to avoid broken references.
 */
class DonorDashboardService
{
    public function getDonorDashboard(int $donorId): array
    {
        return [
            'deprecated' => true,
            'message' => 'Donor reporting was removed after deleting programs and donor-related flows.',
        ];
    }
}
