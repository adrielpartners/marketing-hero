<?php

declare(strict_types=1);

namespace MarketingHero\Admin;

use MarketingHero\Support\Container;
use MarketingHero\Support\Helpers;

final class PostHandlers
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $actions = [
            'mh_add_activity' => 'addActivity',
            'mh_delete_activity' => 'deleteActivity',
            'mh_add_result' => 'addResult',
            'mh_delete_result' => 'deleteResult',
            'mh_add_campaign' => 'addCampaign',
            'mh_delete_campaign' => 'deleteCampaign',
            'mh_add_channel' => 'addChannel',
            'mh_update_channel' => 'updateChannel',
            'mh_delete_channel' => 'deleteChannel',
            'mh_toggle_channel' => 'toggleChannel',
            'mh_add_result_category' => 'addResultCategory',
            'mh_update_result_category' => 'updateResultCategory',
            'mh_delete_result_category' => 'deleteResultCategory',
            'mh_toggle_result_category' => 'toggleResultCategory',
            'mh_save_settings' => 'saveSettings',
        ];

        foreach ($actions as $action => $method) {
            add_action('admin_post_' . $action, [$this, $method]);
        }
    }

    public function addActivity(): void
    {
        $this->verify('mh_add_activity');

        $occurredAtUtc = Helpers::toUtcDatetime((string) sanitize_text_field($_POST['occurred_at'] ?? ''));
        $teamHoursFloat = (float) ($_POST['team_hours'] ?? 0);
        $ownerHoursFloat = (float) ($_POST['owner_hours'] ?? 0);

        $this->container->activities()->create([
            'occurred_at' => $occurredAtUtc,
            'channel_id' => absint($_POST['channel_id'] ?? 0) ?: $this->container->channels()->getUncategorizedId(),
            'quantity' => absint($_POST['quantity'] ?? 1),
            'cash_investment_cents' => $this->parseMoneyToCents((string) ($_POST['cash_investment'] ?? '0')),
            'team_time_minutes' => (int) round($teamHoursFloat * 60),
            'owner_time_minutes' => (int) round($ownerHoursFloat * 60),
            'campaign_id' => absint($_POST['campaign_id'] ?? 0) ?: null,
            'source' => sanitize_text_field((string) ($_POST['source'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($_POST['notes'] ?? '')),
            'meta_json' => null,
        ]);

        $returnPage = sanitize_key((string) ($_POST['return_page'] ?? 'marketing-hero-dashboard'));
        $targetPage = in_array($returnPage, ['marketing-hero-dashboard', 'marketing-hero-activities'], true) ? $returnPage : 'marketing-hero-dashboard';
        wp_safe_redirect(Helpers::pageUrl($targetPage, array_merge(['mh_notice' => 'activity_added'], $this->rangeArgs())));
        exit;
    }

    public function deleteActivity(): void
    {
        $this->verify('mh_delete_activity');
        $id = absint($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->container->activities()->delete($id);
        }

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-activities', ['mh_notice' => 'deleted']));
        exit;
    }

    public function addResult(): void
    {
        $this->verify('mh_add_result');

        $occurredAtUtc = Helpers::toUtcDatetime((string) sanitize_text_field($_POST['occurred_at'] ?? ''));
        $this->container->results()->create([
            'occurred_at' => $occurredAtUtc,
            'result_category_id' => absint($_POST['result_category_id'] ?? 0) ?: $this->container->resultCategories()->getUncategorizedId(),
            'channel_id' => absint($_POST['channel_id'] ?? 0) ?: $this->container->channels()->getUncategorizedId(),
            'value_cents' => $this->parseMoneyToCents((string) ($_POST['value'] ?? '0')),
            'campaign_id' => absint($_POST['campaign_id'] ?? 0) ?: null,
            'source' => sanitize_text_field((string) ($_POST['source'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($_POST['notes'] ?? '')),
            'meta_json' => null,
        ]);

        $returnPage = sanitize_key((string) ($_POST['return_page'] ?? 'marketing-hero-dashboard'));
        $targetPage = in_array($returnPage, ['marketing-hero-dashboard', 'marketing-hero-results'], true) ? $returnPage : 'marketing-hero-dashboard';
        wp_safe_redirect(Helpers::pageUrl($targetPage, array_merge(['mh_notice' => 'result_added'], $this->rangeArgs())));
        exit;
    }

    public function deleteResult(): void
    {
        $this->verify('mh_delete_result');
        $id = absint($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->container->results()->delete($id);
        }

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-results', ['mh_notice' => 'deleted']));
        exit;
    }

    public function addCampaign(): void
    {
        $this->verify('mh_add_campaign');
        $this->container->campaigns()->create([
            'name' => sanitize_text_field((string) ($_POST['name'] ?? '')),
            'start_date' => sanitize_text_field((string) ($_POST['start_date'] ?? '')) ?: null,
            'end_date' => sanitize_text_field((string) ($_POST['end_date'] ?? '')) ?: null,
            'notes' => sanitize_textarea_field((string) ($_POST['notes'] ?? '')),
            'meta_json' => null,
        ]);

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-campaigns', ['mh_notice' => 'added']));
        exit;
    }

    public function deleteCampaign(): void
    {
        $this->verify('mh_delete_campaign');
        $id = absint($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->container->campaigns()->delete($id);
        }

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-campaigns', ['mh_notice' => 'deleted']));
        exit;
    }

    public function addChannel(): void
    {
        $this->verify('mh_add_channel');
        $this->container->channels()->create(
            sanitize_text_field((string) ($_POST['name'] ?? '')),
            sanitize_key((string) ($_POST['category'] ?? 'organic'))
        );
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'channel_added']));
        exit;
    }

    public function updateChannel(): void
    {
        $this->verify('mh_update_channel');
        $this->container->channels()->update(absint($_POST['id'] ?? 0), sanitize_text_field((string) ($_POST['name'] ?? '')), sanitize_key((string) ($_POST['category'] ?? 'organic')));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'channel_updated']));
        exit;
    }

    public function deleteChannel(): void
    {
        $this->verify('mh_delete_channel');
        $this->container->channels()->deleteAndReassignToUncategorized(absint($_POST['id'] ?? 0));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'channel_deleted']));
        exit;
    }

    public function toggleChannel(): void
    {
        $this->verify('mh_toggle_channel');
        $this->container->channels()->toggle(absint($_POST['id'] ?? 0), !empty($_POST['is_active']));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'channel_toggled']));
        exit;
    }

    public function addResultCategory(): void
    {
        $this->verify('mh_add_result_category');
        $this->container->resultCategories()->create(sanitize_text_field((string) ($_POST['name'] ?? '')));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'category_added']));
        exit;
    }

    public function updateResultCategory(): void
    {
        $this->verify('mh_update_result_category');
        $this->container->resultCategories()->update(absint($_POST['id'] ?? 0), sanitize_text_field((string) ($_POST['name'] ?? '')));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'category_updated']));
        exit;
    }

    public function deleteResultCategory(): void
    {
        $this->verify('mh_delete_result_category');
        $this->container->resultCategories()->deleteAndReassignToUncategorized(absint($_POST['id'] ?? 0));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'category_deleted']));
        exit;
    }

    public function toggleResultCategory(): void
    {
        $this->verify('mh_toggle_result_category');
        $this->container->resultCategories()->toggle(absint($_POST['id'] ?? 0), !empty($_POST['is_active']));
        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'category_toggled']));
        exit;
    }

    public function saveSettings(): void
    {
        $this->verify('mh_save_settings');
        $this->container->settings()->set('team_time_cost_per_hour_cents', (string) absint($_POST['team_time_cost_per_hour_cents'] ?? 0));
        $this->container->settings()->set('owner_time_cost_per_hour_cents', (string) absint($_POST['owner_time_cost_per_hour_cents'] ?? 0));

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-settings', ['mh_notice' => 'saved']));
        exit;
    }

    private function verify(string $action): void
    {
        if (!current_user_can('manage_marketing_hero')) {
            wp_die(esc_html__('Permission denied.', 'marketing-hero'));
        }

        check_admin_referer($action);
    }

    private function parseMoneyToCents(string $amount): int
    {
        $normalized = preg_replace('/[^\d\.]/', '', $amount) ?? '0';
        return (int) round(((float) $normalized) * 100);
    }

    private function rangeArgs(): array
    {
        return [
            'mh_range' => sanitize_key((string) ($_POST['mh_range'] ?? 'wtd')),
            'mh_from' => sanitize_text_field((string) ($_POST['mh_from'] ?? '')),
            'mh_to' => sanitize_text_field((string) ($_POST['mh_to'] ?? '')),
        ];
    }
}
