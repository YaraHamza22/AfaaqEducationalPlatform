<?php
namespace Modules\AssesmentModule\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case SHORT_ANSWER = 'short_answer';
}