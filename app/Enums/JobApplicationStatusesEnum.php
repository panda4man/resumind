<?php

namespace App\Enums;

enum JobApplicationStatusesEnum: string
{
    case Prospecting = "prospecting";
    case Applied = "applied";
    case Interviewing = "interviewing";
    case Offer = "offer";
    case Rejected = "rejected";
    case Withdrawn = "withdrawn";
}