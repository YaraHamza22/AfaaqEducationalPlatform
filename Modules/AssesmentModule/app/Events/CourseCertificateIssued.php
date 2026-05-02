<?php

namespace Modules\AssesmentModule\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\AssesmentModule\Models\CourseCertificate;

class CourseCertificateIssued
{
    use Dispatchable, SerializesModels;

    public function __construct(public CourseCertificate $certificate)
    {
    }
}
