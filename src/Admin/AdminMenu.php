<?php

declare(strict_types=1);

namespace MarketingHero\Admin;

use MarketingHero\Services\DateRange;
use MarketingHero\Support\Container;

final class AdminMenu
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
    }

    public function registerMenus(): void
    {
        $cap = 'manage_marketing_hero';

        add_menu_page('Marketing Hero', 'Marketing Hero', $cap, 'marketing-hero-dashboard', [$this, 'renderDashboard'], 'dashicons-chart-line', 58);
        add_submenu_page('marketing-hero-dashboard', 'Dashboard', 'Dashboard', $cap, 'marketing-hero-dashboard', [$this, 'renderDashboard']);
        add_submenu_page('marketing-hero-dashboard', 'Activities', 'Activities', $cap, 'marketing-hero-activities', [$this, 'renderActivities']);
        add_submenu_page('marketing-hero-dashboard', 'Results', 'Results', $cap, 'marketing-hero-results', [$this, 'renderResults']);
        add_submenu_page('marketing-hero-dashboard', 'Campaigns', 'Campaigns', $cap, 'marketing-hero-campaigns', [$this, 'renderCampaigns']);
        add_submenu_page('marketing-hero-dashboard', 'Settings', 'Settings', $cap, 'marketing-hero-settings', [$this, 'renderSettings']);
    }

    public function renderDashboard(): void
    {
        $this->guard();

        $range = DateRange::fromQueryParams($_GET);
        $dashboard = $this->container->dashboard();

        $data = [
            'range' => $range,
            'kpis' => $dashboard->getKpis($range),
            'inputs' => $dashboard->getInputsSummary($range),
            'outputs' => $dashboard->getOutputsSummary($range),
        ];

        $this->renderTemplate('dashboard', $data);
    }

    public function renderActivities(): void
    {
        $this->guard();
        $range = DateRange::fromQueryParams($_GET);
        $data = [
            'range' => $range,
            'activities' => $this->container->activities()->list($range, ['limit' => 100]),
            'campaigns' => $this->container->campaigns()->listAll(),
        ];

        $this->renderTemplate('activities', $data);
    }

    public function renderResults(): void
    {
        $this->guard();
        $range = DateRange::fromQueryParams($_GET);
        $data = [
            'range' => $range,
            'results' => $this->container->results()->list($range, ['limit' => 100]),
            'campaigns' => $this->container->campaigns()->listAll(),
        ];

        $this->renderTemplate('results', $data);
    }

    public function renderCampaigns(): void
    {
        $this->guard();
        $data = [
            'campaigns' => $this->container->campaigns()->listAll(),
        ];

        $this->renderTemplate('campaigns', $data);
    }

    public function renderSettings(): void
    {
        $this->guard();
        $this->renderTemplate('settings', []);
    }

    private function guard(): void
    {
        if (!current_user_can('manage_marketing_hero')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'marketing-hero'));
        }
    }

    private function renderTemplate(string $name, array $data): void
    {
        $template = MARKETING_HERO_PATH . 'templates/admin/' . $name . '.php';
        if (!file_exists($template)) {
            return;
        }

        extract($data, EXTR_SKIP);
        include $template;
    }
}
