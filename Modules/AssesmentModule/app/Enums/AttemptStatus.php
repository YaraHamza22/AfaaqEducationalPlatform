<?php
namespace Modules\AssesmentModule\Enums;
enum AttemptStatus :string {
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case GRADED =   'graded';
}