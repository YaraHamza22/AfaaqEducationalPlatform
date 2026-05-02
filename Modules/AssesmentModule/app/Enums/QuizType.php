<?php

namespace Modules\AssesmentModule\Enums;

enum QuizType: string{

  case COURSE = 'course';
  case LESSON = 'lesson';
  case UNIT  = 'unit';
}