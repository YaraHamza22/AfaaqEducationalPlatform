<?php

namespace Modules\AssesmentModule\Enums;    
enum QuizStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
