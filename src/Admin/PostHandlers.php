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
        add_action('admin_post_mh_add_activity', [$this, 'addActivity']);
        add_action('admin_post_mh_delete_activity', [$this, 'deleteActivity']);
        add_action('admin_post_mh_add_result', [$this, 'addResult']);
        add_action('admin_post_mh_delete_result', [$this, 'deleteResult']);
        add_action('admin_post_mh_add_campaign', [$this, 'addCampaign']);
        add_action('admin_post_mh_delete_campaign', [$this, 'deleteCampaign']);
    }

    public function addActivity(): void
    {
        $this->verify('mh_add_activity');

        $occurredAtUtc = Helpers::toUtcDatetime((string) sanitize_text_field($_POST['occurred_at'] ?? ''));

        $this->container->activities()->create([
            'occurred_at' => $occurredAtUtc,
            'type' => sanitize_key((string) ($_POST['type'] ?? '')),
            'quantity' => absint($_POST['quantity'] ?? 1),
            'cost_cents' => absint($_POST['cost_cents'] ?? 0),
            'campaign_id' => absint($_POST['campaign_id'] ?? 0) ?: null,
            'source' => sanitize_text_field((string) ($_POST['source'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($_POST['notes'] ?? '')),
            'meta_json' => null,
        ]);

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-activities', ['mh_notice' => 'added']));
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
            'type' => sanitize_key((string) ($_POST['type'] ?? 'lead')),
            'value_cents' => absint($_POST['value_cents'] ?? 0),
            'campaign_id' => absint($_POST['campaign_id'] ?? 0) ?: null,
            'source' => sanitize_text_field((string) ($_POST['source'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($_POST['notes'] ?? '')),
            'meta_json' => null,
        ]);

        wp_safe_redirect(Helpers::pageUrl('marketing-hero-results', ['mh_notice' => 'added']));
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

    private function verify(string $action): void
    {
        if (!current_user_can('manage_marketing_hero')) {
            wp_die(esc_html__('Permission denied.', 'marketing-hero'));
        }

        check_admin_referer($action);
    }
}
