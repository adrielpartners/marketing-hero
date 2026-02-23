<?php

declare(strict_types=1);

namespace MarketingHero\Services;

use DateTimeImmutable;
use DateTimeZone;

final class DateRange
{
    public function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end,
        private string $preset = 'wtd'
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

        return new self($start, $now->setTime(23, 59, 59), 'wtd');
    }

    public static function last7Days(): self
    {
        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        return new self($now->modify('-6 days')->setTime(0, 0, 0), $now->setTime(23, 59, 59), 'l7');
    }

    public static function monthToDate(): self
    {
        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        return new self($now->modify('first day of this month')->setTime(0, 0, 0), $now->setTime(23, 59, 59), 'mtd');
    }

    public static function fromQueryParams(array $query): self
    {
        $preset = isset($query['mh_range']) ? sanitize_text_field((string) $query['mh_range']) : 'wtd';

        return match ($preset) {
            'l7' => self::last7Days(),
            'mtd' => self::monthToDate(),
            'custom' => self::fromCustom($query),
            default => self::weekToDate(),
        };
    }

    private static function fromCustom(array $query): self
    {
        $tz = wp_timezone();
        $startRaw = isset($query['mh_from']) ? sanitize_text_field((string) $query['mh_from']) : '';
        $endRaw = isset($query['mh_to']) ? sanitize_text_field((string) $query['mh_to']) : '';

        $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startRaw . ' 00:00:00', $tz);
        $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endRaw . ' 23:59:59', $tz);

        if ($start instanceof DateTimeImmutable && $end instanceof DateTimeImmutable && $start <= $end) {
            return new self($start, $end, 'custom');
        }

        return self::weekToDate();
    }

    public function toQueryArgs(): array
    {
        $args = ['mh_range' => $this->preset];
        if ($this->preset === 'custom') {
            $args['mh_from'] = $this->start->format('Y-m-d');
            $args['mh_to'] = $this->end->format('Y-m-d');
        }

        return $args;
    }

    public function label(): string
    {
        return $this->start->format('M j, Y') . ' – ' . $this->end->format('M j, Y');
    }

    public function startUtc(DateTimeZone $utc): string
    {
        return $this->start->setTimezone($utc)->format('Y-m-d H:i:s');
    }

    public function endUtc(DateTimeZone $utc): string
    {
        return $this->end->setTimezone($utc)->format('Y-m-d H:i:s');
    }
}
