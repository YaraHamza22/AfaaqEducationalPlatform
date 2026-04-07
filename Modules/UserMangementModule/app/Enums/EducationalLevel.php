<?php
namespace Modules\UserMangementModule\Enums;

enum EducationalLevel: string
{
    case HIGHSCHOOL = 'highschool';
    case COLLAGE = 'collage';

    case MASTER = 'master';
    case DOCTORATE = 'doctorate';
    case OTHER = 'other';
}