<?php

namespace Modules\ReportingModule\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * Deprecated: donor dashboard was removed after deleting programs.
 */
class DonorDashboardController extends Controller
{
    public function dashboard(int $donorId): JsonResponse
    {
        return $this->error('Donor reporting was removed after deleting programs and donor-related flows.', 410);
    }
}
