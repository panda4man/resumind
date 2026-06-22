<?php

namespace App\Enums;

enum StatusEventName: string
{
    case Submitted = 'submitted';
    case Responded = 'responded';
    case Interviewing = 'interviewing';
    case Offer = 'offer';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
