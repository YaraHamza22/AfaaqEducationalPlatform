<?php
namespace Modules\AssesmentModule\Enums;

enum QuestionType: string
{
    case MCQ = 'mcq';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TRUE_FALSE = 'true_false';
    case TEXT = 'text';
    case SHORT_ANSWER = 'short_answer';

    public function isMcq(): bool
    {
        return $this === self::MCQ || $this === self::MULTIPLE_CHOICE;
    }

    public function isShortAnswer(): bool
    {
        return $this === self::TEXT || $this === self::SHORT_ANSWER;
    }
}
