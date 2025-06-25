<?php

namespace App\Enums;

enum JobApplicationStatusesEnum: string
{
    case Applied = "applied";
    case Interviewing = "interviewing";
    case Offer = "offer";
    case Rejected = "rejected";
    case Withdrawn = "withdrawn";
}