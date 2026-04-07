<?php

namespace Modules\UserMangementModule\Enums;

enum UserRole:string
{
    case SUPERADMIN = 'super-admin';
    case ADMIN = 'admin';
    case INSTRUCTOR = 'instructor';
    case STUDENT = 'student';
    case AUDITOR = 'auditor';
}