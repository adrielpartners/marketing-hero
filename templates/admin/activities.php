<?php
/** @var MarketingHero\Services\DateRange $range */
/** @var array $activities */
/** @var array $campaigns */
/** @var array $channels */

use MarketingHero\Support\Helpers;
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header"><div><h1 class="mh-title">Activities</h1><p class="mh-subtitle"><?php echo esc_html($range->label()); ?></p></div></div>

    <?php if (isset($_GET['mh_notice'])) : ?>
      <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sanitize_text_field((string) $_GET['mh_notice'])); ?></p></div>
    <?php endif; ?>

    <div class="mh-grid mh-grid--2">
      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Activity</h2></div><div class="mh-card__body">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="mh_add_activity" />
          <?php wp_nonce_field('mh_add_activity'); ?>
          <input type="hidden" name="return_page" value="marketing-hero-activities" />

          <div class="mh-field"><label class="mh-label">Activity Date</label><input class="mh-input" type="datetime-local" name="occurred_at" required></div>
          <div class="mh-field"><label class="mh-label">Campaign (how you group your marketing efforts)</label><select class="mh-select" name="campaign_id"><option value="">None</option><?php foreach ($campaigns as $campaign) : ?><option value="<?php echo esc_attr((string) $campaign['id']); ?>"><?php echo esc_html((string) $campaign['name']); ?></option><?php endforeach; ?></select></div>
          <div class="mh-field"><label class="mh-label">Channel (ex. Search Ad, Door Knocking, Postcard)</label><select class="mh-select" name="channel_id" required><?php foreach ($channels as $group => $groupChannels) : ?><optgroup label="<?php echo esc_attr(ucfirst($group)); ?>"><?php foreach ($groupChannels as $channel) : ?><option value="<?php echo esc_attr((string) $channel['id']); ?>"><?php echo esc_html((string) $channel['name']); ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
          <div class="mh-field"><label class="mh-label">Source (ex. Google, Oak Ridge Subdivision, 33982 Mailing)</label><input class="mh-input" type="text" name="source"></div>
          <div class="mh-field"><label class="mh-label">Quantity (ex. how many impressions, how many doors, how many postcards)</label><input class="mh-input" type="number" min="1" name="quantity" value="1" required></div>
          <div class="mh-field"><label class="mh-label">Cash Investment (in dollars)</label><input class="mh-input" type="text" name="cash_investment"></div>
          <div class="mh-field"><label class="mh-label">Team Time Investment (in hours)</label><input class="mh-input" type="number" step="0.25" min="0" name="team_hours" value="0"></div>
          <div class="mh-field"><label class="mh-label">Owner Time Investment (in hours)</label><input class="mh-input" type="number" step="0.25" min="0" name="owner_hours" value="0"></div>
          <div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div>
          <button class="mh-btn mh-btn--primary" type="submit">Add Activity</button>
        </form>
      </div></article>

      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Recent Activities</h2></div><div class="mh-card__body"><div class="mh-list">
        <?php foreach ($activities as $activity) : ?>
          <div class="mh-item">
            <div class="mh-item__left">
              <p class="mh-item__title"><?php echo esc_html((string) ($activity['channel_name'] ?? 'Uncategorized')); ?></p>
              <p class="mh-item__meta"><?php echo esc_html((string) $activity['occurred_at'] . ' · Qty ' . (int) $activity['quantity']); ?></p>
              <p class="mh-item__meta"><?php echo esc_html('Cash: ' . Helpers::formatMoneyCents((int) ($activity['cash_investment_cents'] ?? 0))); ?></p>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
              <input type="hidden" name="action" value="mh_delete_activity" />
              <input type="hidden" name="id" value="<?php echo esc_attr((string) $activity['id']); ?>" />
              <?php wp_nonce_field('mh_delete_activity'); ?>
              <button class="mh-btn" data-mh-confirm="Delete this activity?" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div></div></article>
    </div>
  </div>
</div>
