<?php

namespace Modules\AssesmentModule\Services\V1;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\AssesmentModule\Models\CourseCertificate;
use RuntimeException;

class CertificateService
{
    public function __construct(
        private CourseQuizProgressService $progressService,
        private CertificateEligibilityService $eligibilityService
    ) {
    }

    public function buildCourseCertificatePdf(int $courseId, int $studentId): array
    {
        $progress = $this->progressService->build($courseId, $studentId);
        $certificateStatus = $this->eligibilityService->evaluateAndIssue($courseId, $studentId, $progress);

        if (($certificateStatus['issued'] ?? false) !== true) {
            throw new RuntimeException('Certificate is not available yet. Student must complete and pass all required quizzes with at least 60%.');
        }

        $certificate = CourseCertificate::query()
            ->with(['course', 'student'])
            ->where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (! $certificate) {
            throw new RuntimeException('Certificate record not found.');
        }

        $filename = sprintf(
            'course-certificate-%d-%d.pdf',
            $courseId,
            $studentId
        );

        $pdf = Pdf::loadView('assesmentmodule::certificates.course', [
            'certificate' => $certificate,
            'progress' => $progress,
        ])->setPaper('a4', 'landscape');

        return [
            'filename' => $filename,
            'content' => $pdf->output(),
        ];
    }
}
