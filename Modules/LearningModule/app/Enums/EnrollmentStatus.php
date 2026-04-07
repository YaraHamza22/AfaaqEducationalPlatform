<?php

namespace Modules\LearningModule\Enums;

enum EnrollmentStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case DROPPED = 'dropped';
    case SUSPENDED = 'suspended';
}
