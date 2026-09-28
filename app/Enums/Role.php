<?php

namespace App\Enums;

enum Role: string
{
    case Aspirante = 'aspirante';
    case Aprendiz = 'aprendiz';
    case Instructor = 'instructor';
}
