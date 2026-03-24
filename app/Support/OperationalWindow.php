<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class OperationalWindow
{
    /**
     * @return array<int, array{start: string, end: string}>
     */
    public static function parseWindows(string $windows): array
    {
        $parsed = [];

        foreach (explode(',', $windows) as $window) {
            $candidate = trim($window);
            if ($candidate === '' || ! str_contains($candidate, '-')) {
                continue;
            }

            [$start, $end] = array_map('trim', explode('-', $candidate, 2));
            if (! self::isValidTime($start) || ! self::isValidTime($end) || $start === $end) {
                continue;
            }

            $parsed[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        return $parsed;
    }

    public static function isNowInAnyWindow(
        string $windows,
        string $timezone = 'UTC',
        ?CarbonInterface $now = null
    ): bool {
        $current = $now ? CarbonImmutable::instance($now) : CarbonImmutable::now($timezone);
        $time = $current->setTimezone($timezone)->format('H:i');

        foreach (self::parseWindows($windows) as $window) {
            if (self::containsTime($window['start'], $window['end'], $time)) {
                return true;
            }
        }

        return false;
    }

    public static function containsTime(string $start, string $end, string $time): bool
    {
        if (! self::isValidTime($start) || ! self::isValidTime($end) || ! self::isValidTime($time)) {
            return false;
        }

        if ($start < $end) {
            return $time >= $start && $time <= $end;
        }

        return $time >= $start || $time <= $end;
    }

    private static function isValidTime(string $time): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }
}

