<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use DateTimeZone;

abstract class AbstractDbRepository
{
    protected \wpdb $wpdb;
    protected DateTimeZone $utc;

    public function __construct(?\wpdb $wpdbInstance = null)
    {
        $this->wpdb = $wpdbInstance instanceof \wpdb ? $wpdbInstance : $GLOBALS['wpdb'];
        $this->utc = new DateTimeZone('UTC');
    }

    protected function table(string $suffix): string
    {
        return $this->wpdb->prefix . $suffix;
    }
}
