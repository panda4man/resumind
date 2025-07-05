<?php

namespace App\Enums;

enum InterviewFormatsEnum: string
{
    case Phone = 'phone';
    case Zoom = 'zoom';
    case Teams = 'teams';
    case GoogleMeet = 'google-meet';
    case InPerson = 'in-person';
    case Other = 'other';
}
