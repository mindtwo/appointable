<?php

namespace mindtwo\Appointable\Helper;

use Illuminate\Support\Carbon;

// TODO clean up
class TimesAndDates
{
    /**
     * --- TIME ---
     */
    public static function convertTimeToUserTimezone(string|Carbon $time): Carbon
    {
        $time = Carbon::parse($time)->timezone(config('appointable.timezone', 'UTC'));

        return $time;
    }

    public static function formatTimeToUserTimezone(string|Carbon $time, string $format = 'H:i'): string
    {
        $time = self::convertTimeToUserTimezone($time);

        return $time->format($format);
    }

    public static function convertTimeToUtc(string|Carbon $time): Carbon
    {
        $time = Carbon::parse($time, config('appointable.timezone', 'UTC'))->utc();

        return $time;
    }

    public static function formatTimeToUtc(string|Carbon $time, string $format = 'H:i'): string
    {
        $time = self::convertTimeToUtc($time);

        return $time->format($format);
    }

    /**
     * --- DATE ---
     */
    public static function convertDateToUtc(string|Carbon $date): Carbon
    {
        [$d, $time] = self::convertDateAndTimeToUtc($date);

        return $d;
    }

    /**
     * Returns an array with the date at index "0" and time at index "1" in UTC.
     *
     * @param  string|Carbon  $date  - The date and time in the user's timezone.
     * @return array<mixed> - The date and time in UTC.
     */
    public static function convertDateAndTimeToUtc(string|Carbon $date): array
    {
        $carbon = Carbon::parse($date, config('appointable.timezone', 'UTC'));

        $date = $carbon->copy()->setTime(0, 0, 0);
        $utcTime = $carbon->copy()->utc();

        if ($utcTime->hour > $carbon->hour) {
            $date->subDay();
        }

        return [$date, $utcTime];
    }

    /**
     * Returns an array with the date at index "0" and time at index "1" in UTC as string.
     *
     * @param  string|Carbon  $date  - The date and time in the user's timezone.
     * @return array<mixed> - The date and time in UTC.
     */
    public static function formatDateAndTimeToUtc(string|Carbon $date, string $dateFormat = 'Y-m-d', string $timeFormat = 'H:i'): array
    {
        [$date, $time] = self::convertDateAndTimeToUtc($date);

        return [$date->format($dateFormat), $time->format($timeFormat)];
    }

    /**
     * Returns an array with the date at index "0" and time at index "1" in UTC.
     *
     * @param  string|Carbon  $date  - The date and time in the user's timezone.
     * @return array<mixed> - The date and time in UTC.
     */
    public static function convertDateAndTimeToUserTime(string|Carbon $date): array
    {
        $carbon = Carbon::parse($date);

        $date = $carbon->copy()->setTime(0, 0, 0);
        $userTime = $carbon->copy()->timezone(config('appointable.timezone', 'UTC'));

        if ($userTime->hour < $carbon->hour) {
            $date->addDay();
        }

        return [$date, $userTime];
    }

    /**
     * Returns an array with the date at index "0" and time at index "1" in UTC as string.
     *
     * @param  string|Carbon  $date  - The date and time in the user's timezone.
     * @return array<mixed> - The date and time in UTC.
     */
    public static function formatDateAndTimeToUserTime(string|Carbon $date, string $dateFormat = 'Y-m-d', string $timeFormat = 'H:i'): array
    {
        [$date, $time] = self::convertDateAndTimeToUserTime($date);

        return [$date->format($dateFormat), $time->format($timeFormat)];
    }

    public static function convertDateToUserTimezone(string|Carbon $date): Carbon
    {
        [$d, $t] = self::convertDateAndTimeToUserTime($date);

        return $d;
    }
}
