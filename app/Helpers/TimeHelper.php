<?php

namespace App\Helpers;

class TimeHelper
{
    public static function minutesToHoursMinutes($minutes)
    {
        if (is_null($minutes) || $minutes === 0) {
            return '0 minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours == 0) {
            // Only minutes, no hours
            return sprintf(
                '%d %s',
                $remainingMinutes,
                $remainingMinutes == 1 ? 'minute' : 'minutes'
            );
        } elseif ($remainingMinutes == 0) {
            // Only hours, no minutes
            return sprintf(
                '%d %s',
                $hours,
                $hours == 1 ? 'hour' : 'hours'
            );
        } else {
            // Both hours and minutes
            return sprintf(
                '%d %s %d %s',
                $hours,
                $hours == 1 ? 'hour' : 'hours',
                $remainingMinutes,
                $remainingMinutes == 1 ? 'minute' : 'minutes'
            );
        }
    }
}
