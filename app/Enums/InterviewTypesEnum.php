<?php

namespace App\Enums;

enum InterviewTypesEnum: string
{
    case Screening = 'screening';
    case Technical = 'technical';
    case Hr = 'hr';
    case Ceo = 'ceo';
    case Panel = 'panel';
    case Final = 'final';
    case Other = 'other';
}
