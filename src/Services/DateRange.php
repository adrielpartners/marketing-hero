<?php

declare(strict_types=1);

namespace MarketingHero\Services;

use DateTimeImmutable;
use DateTimeZone;

final class DateRange
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end
    ) {
    }

    public static function weekToDate(): self
    {
        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        $weekStart = (int) get_option('start_of_week', 1);
        $dayOfWeek = (int) $now->format('w');
        $offset = ($dayOfWeek - $weekStart + 7) % 7;
        $start = $now->modify(sprintf('-%d days', $offset))->setTime(0, 0, 0);

        return new self($start, $now->setTime(23, 59, 59));
    }

    public static function lastWeek(): self
    {
        $tz = wp_timezone();
        $weekStart = (int) get_option('start_of_week', 1);
        $now = new DateTimeImmutable('now', $tz);
        $dayOfWeek = (int) $now->format('w');
        $offset = ($dayOfWeek - $weekStart + 7) % 7;

        $currentWeekStart = $now->modify(sprintf('-%d days', $offset))->setTime(0, 0, 0);
        $start = $currentWeekStart->modify('-7 days');
        $end = $currentWeekStart->modify('-1 second');

        return new self($start, $end);
    }

    public static function monthToDate(): self
    {
        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        $start = $now->modify('first day of this month')->setTime(0, 0, 0);

        return new self($start, $now->setTime(23, 59, 59));
    }

    public static function fromQueryParams(array $query): self
    {
        $preset = isset($query['range']) ? sanitize_text_field((string) $query['range']) : 'wtd';

        if ($preset === 'last_week') {
            return self::lastWeek();
        }

        if ($preset === 'mtd') {
            return self::monthToDate();
        }

        if ($preset === 'custom') {
            $tz = wp_timezone();
            $startRaw = isset($query['start']) ? sanitize_text_field((string) $query['start']) : '';
            $endRaw = isset($query['end']) ? sanitize_text_field((string) $query['end']) : '';

            $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startRaw . ' 00:00:00', $tz);
            $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endRaw . ' 23:59:59', $tz);

            if ($start instanceof DateTimeImmutable && $end instanceof DateTimeImmutable && $start <= $end) {
                return new self($start, $end);
            }
        }

        return self::weekToDate();
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function startUtc(DateTimeZone $utc): string
    {
        return $this->start->setTimezone($utc)->format('Y-m-d H:i:s');
    }

    public function endUtc(DateTimeZone $utc): string
    {
        return $this->end->setTimezone($utc)->format('Y-m-d H:i:s');
    }

    public function label(): string
    {
        return $this->start->format('M j, Y') . ' – ' . $this->end->format('M j, Y');
    }
}
