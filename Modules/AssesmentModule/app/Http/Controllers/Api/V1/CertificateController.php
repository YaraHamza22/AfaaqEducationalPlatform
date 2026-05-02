<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AssesmentModule\Services\V1\CertificateService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService)
    {
    }

    public function download(Request $request, int $courseId)
    {
        $studentId = (int) ($request->input('student_id') ?: Auth::id());
        if ($studentId !== (int) Auth::id() && ! $this->canDownloadForOthers($request)) {
            return self::error('You are not allowed to download certificate for this student.', Response::HTTP_FORBIDDEN);
        }

        try {
            $result = $this->certificateService->buildCourseCertificatePdf($courseId, $studentId);

            return response($result['content'], Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
            ]);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function canDownloadForOthers(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin'))) {
            return true;
        }

        return method_exists($user, 'hasPermissionTo')
            && ($user->hasPermissionTo('list-students', 'api') || $user->hasPermissionTo('show-student', 'api'));
    }
}
