<?php

namespace Modules\ReportingModule\Services;

/**
 * Deprecated service kept only to avoid broken references.
 */
class PartnerOrganizationDashboardService
{
    public function getPartnerOrganizationDashboard(int $organizationId): array
    {
        return [
            'deprecated' => true,
            'message' => 'Organization reporting was removed after deleting managers, organizations, and programs.',
        ];
    }

    public function generateComprehensiveReport(array $filters): array
    {
        return [
            'deprecated' => true,
            'message' => 'Organization reporting was removed after deleting managers, organizations, and programs.',
        ];
    }
}
