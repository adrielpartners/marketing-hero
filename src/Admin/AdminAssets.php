<?php

declare(strict_types=1);

namespace MarketingHero\Admin;

final class AdminAssets
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hook): void
    {
        if (strpos($hook, 'marketing-hero') === false) {
            return;
        }

        wp_enqueue_style(
            'marketing-hero-admin',
            MARKETING_HERO_URL . 'assets/admin/marketing-hero-admin.css',
            [],
            MARKETING_HERO_VERSION
        );

        wp_enqueue_script(
            'marketing-hero-admin',
            MARKETING_HERO_URL . 'assets/admin/marketing-hero-admin.js',
            [],
            MARKETING_HERO_VERSION,
            true
        );
    }
}
