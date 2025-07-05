<?php

namespace App\Actions;

use App\Models\Interview;

class InterviewLengthToHumanLabel
{
    public function handle(Interview $interview): string
    {
        if (!$interview->length_minutes || $interview->length_minutes === 0) {
            return '—';
        }

        $hours = floor($interview->length_minutes / 60);
        $mins = $interview->length_minutes % 60;

        return match (true) {
            $hours > 0 && $mins > 0 => "{$hours}h {$mins}m",
            $hours > 0 => "{$hours}h",
            default => "{$mins}m",
        };
    }
}
