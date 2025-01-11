<?php

namespace App\Traits;

use Carbon\Carbon;

trait HandlesTimezones
{
    protected function convertToUTC($localTime, $timezone = null)
    {
        if (!$localTime) return null;

        try {
            $userTimezone = $timezone ?? auth()->user()->timezone ?? 'Asia/Manila';
            return Carbon::parse($localTime, $userTimezone)->setTimezone('UTC');
        } catch (\Exception $e) {
            \Log::error('Error converting to UTC: ' . $e->getMessage());
            return null;
        }
    }

    protected function convertToLocal($utcTime, $timezone = null)
    {
        if (!$utcTime) return null;

        try {
            $userTimezone = $timezone ?? auth()->user()->timezone ?? 'Asia/Manila';
            return Carbon::parse($utcTime)->setTimezone($userTimezone);
        } catch (\Exception $e) {
            \Log::error('Error converting to local time: ' . $e->getMessage());
            return null;
        }
    }

    protected function formatForDisplay($date, $format = 'Y-m-d H:i:s', $timezone = null)
    {
        if (!$date) return null;

        try {
            return $this->convertToLocal($date, $timezone)->format($format);
        } catch (\Exception $e) {
            \Log::error('Error formatting date: ' . $e->getMessage());
            return null;
        }
    }

    protected function validateTimezone($timezone)
    {
        try {
            Carbon::now($timezone);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getCurrentTime($timezone = null)
    {
        try {
            $userTimezone = $timezone ?? auth()->user()->timezone ?? 'Asia/Manila';
            return Carbon::now($userTimezone);
        } catch (\Exception $e) {
            \Log::error('Error getting current time: ' . $e->getMessage());
            return Carbon::now('UTC');
        }
    }
} 