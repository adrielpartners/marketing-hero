<?php

declare(strict_types=1);

namespace MarketingHero\Support;

final class Helpers
{
    public static function formatMoneyCents(int $cents): string
    {
        return '$' . number_format_i18n($cents / 100, 2);
    }

    public static function pageUrl(string $page, array $args = []): string
    {
        $base = admin_url('admin.php?page=' . $page);

        return add_query_arg($args, $base);
    }

    public static function toUtcDatetime(string $localDate): string
    {
        $tz = wp_timezone();
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $localDate . ' 00:00:00', $tz);

        if (!$dt instanceof \DateTimeImmutable) {
            $dt = new \DateTimeImmutable('now', $tz);
        }

        return $dt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
}
