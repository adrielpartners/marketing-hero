<?php
/** @var MarketingHero\Services\DateRange $range */
/** @var array $kpis */
/** @var array $inputs */
/** @var array $outputs */
/** @var array $campaigns */
/** @var array $channels */
/** @var array $resultCategories */

use MarketingHero\Support\Helpers;

$rangeArgs = $range->toQueryArgs();
$investment = (int) $inputs['total_investment_cents'];
$revenue = (int) $outputs['revenue_cents'];
$roi = $investment > 0 ? ($revenue / $investment) : 0;
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header">
      <div>
        <h1 class="mh-title"><?php echo esc_html__('Marketing Hero Dashboard', 'marketing-hero'); ?></h1>
        <p class="mh-subtitle"><?php echo esc_html($range->label()); ?></p>
      </div>
      <form method="get" class="mh-row mh-range-form">
        <input type="hidden" name="page" value="marketing-hero-dashboard" />
        <select class="mh-select" name="mh_range" data-mh-range>
          <option value="wtd" <?php selected($rangeArgs['mh_range'], 'wtd'); ?>>Week-to-date</option>
          <option value="l7" <?php selected($rangeArgs['mh_range'], 'l7'); ?>>Last 7 days</option>
          <option value="mtd" <?php selected($rangeArgs['mh_range'], 'mtd'); ?>>Month-to-date</option>
          <option value="custom" <?php selected($rangeArgs['mh_range'], 'custom'); ?>>Custom</option>
        </select>
        <input class="mh-input" type="date" name="mh_from" value="<?php echo esc_attr($rangeArgs['mh_from'] ?? ''); ?>" data-mh-custom-range <?php echo ($rangeArgs['mh_range'] ?? 'wtd') === 'custom' ? '' : 'hidden'; ?> />
        <input class="mh-input" type="date" name="mh_to" value="<?php echo esc_attr($rangeArgs['mh_to'] ?? ''); ?>" data-mh-custom-range <?php echo ($rangeArgs['mh_range'] ?? 'wtd') === 'custom' ? '' : 'hidden'; ?> />
        <button class="mh-btn mh-btn--primary" type="submit">Apply</button>
      </form>
    </div>

    <div class="mh-kpis">
      <?php foreach ($kpis['tiles'] as $tile) : ?>
        <div class="mh-kpi"><p class="mh-kpi__label"><?php echo esc_html((string) $tile['label']); ?></p><p class="mh-kpi__value"><?php echo esc_html((string) $tile['count']); ?></p></div>
      <?php endforeach; ?>
      <div class="mh-kpi"><p class="mh-kpi__label">Avg Order Value</p><p class="mh-kpi__value"><?php echo esc_html(Helpers::formatMoneyCents((int) $kpis['aov_cents'])); ?></p></div>
    </div>

    <div class="mh-grid mh-grid--2" style="margin-top:16px;">
      <article class="mh-card">
        <div class="mh-card__head"><h2 class="mh-card__title">Inputs</h2></div>
        <div class="mh-card__body">
          <div class="mh-list">
            <?php foreach ($inputs['totals_by_channel'] as $channel => $totals) : ?>
              <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html($channel); ?></p><p class="mh-item__meta">Qty: <?php echo esc_html((string) $totals['quantity']); ?></p></div><span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents((int) $totals['cash_cents'])); ?></span></div>
            <?php endforeach; ?>
          </div>
          <button class="mh-btn mh-btn--primary" type="button" data-mh-open-modal="mh-add-activity">+ Add Activity</button>
        </div>
      </article>

      <article class="mh-card">
        <div class="mh-card__head"><h2 class="mh-card__title">Outputs</h2></div>
        <div class="mh-card__body">
          <div class="mh-list">
            <?php foreach ($outputs['totals_by_category'] as $category => $count) : ?>
              <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html($category); ?></p></div><span class="mh-pill mh-pill--accent"><?php echo esc_html((string) $count); ?></span></div>
            <?php endforeach; ?>
          </div>
          <button class="mh-btn mh-btn--primary" type="button" data-mh-open-modal="mh-add-result">+ Add Result</button>
        </div>
      </article>
    </div>

    <article class="mh-card" style="margin-top:16px;"><div class="mh-card__head"><h2 class="mh-card__title">Investment & ROI</h2></div><div class="mh-card__body"><div class="mh-list">
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">Cash Investment</p></div><span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents((int) $inputs['cash_cents'])); ?></span></div>
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">Team Time</p><p class="mh-item__meta"><?php echo esc_html((string) $inputs['team_minutes']); ?> min</p></div><span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents((int) $inputs['team_cost_cents'])); ?></span></div>
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">Owner Time</p><p class="mh-item__meta"><?php echo esc_html((string) $inputs['owner_minutes']); ?> min</p></div><span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents((int) $inputs['owner_cost_cents'])); ?></span></div>
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">Total Investment</p></div><span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents($investment)); ?></span></div>
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">ROI</p></div><span class="mh-pill mh-pill--accent"><?php echo esc_html(number_format($roi, 2)) . 'x'; ?></span></div>
      <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title">Net</p></div><span class="mh-pill mh-pill--accent"><?php echo esc_html(Helpers::formatMoneyCents($revenue - $investment)); ?></span></div>
    </div></div></article>
  </div>
</div>

<div class="mh-modal" id="mh-add-activity" hidden><div class="mh-modal__overlay" data-mh-close-modal></div><div class="mh-modal__dialog"><div class="mh-modal__head"><h3>Add Activity</h3><button type="button" data-mh-close-modal>&times;</button></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mh-modal__body"><input type="hidden" name="action" value="mh_add_activity" /><?php wp_nonce_field('mh_add_activity'); ?><input type="hidden" name="mh_range" value="<?php echo esc_attr((string) ($rangeArgs['mh_range'] ?? 'wtd')); ?>" /><input type="hidden" name="mh_from" value="<?php echo esc_attr((string) ($rangeArgs['mh_from'] ?? '')); ?>" /><input type="hidden" name="mh_to" value="<?php echo esc_attr((string) ($rangeArgs['mh_to'] ?? '')); ?>" />
<div class="mh-field"><label class="mh-label">Campaign<br><small class="mh-muted">utm_campaign</small></label><select class="mh-select" name="campaign_id"><?php foreach ($campaigns as $campaign) : ?><option value="<?php echo esc_attr((string) $campaign['id']); ?>"><?php echo esc_html((string) $campaign['name']); ?></option><?php endforeach; ?></select></div>
<div class="mh-field"><label class="mh-label">Channel<br><small class="mh-muted">utm_medium</small></label><select class="mh-select" name="channel_id"><?php foreach ($channels as $group => $rows) : ?><optgroup label="<?php echo esc_attr(ucfirst($group)); ?>"><?php foreach ($rows as $channel) : ?><option value="<?php echo esc_attr((string) $channel['id']); ?>"><?php echo esc_html((string) $channel['name']); ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
<div class="mh-field"><label class="mh-label">Occurred date/time</label><input class="mh-input" type="datetime-local" name="occurred_at" required></div>
<div class="mh-field"><label class="mh-label">Quantity</label><input class="mh-input" type="number" name="quantity" value="1" min="1"></div>
<div class="mh-field"><label class="mh-label">Cash Investment ($)</label><input class="mh-input" type="text" name="cash_investment"></div>
<div class="mh-row"><div class="mh-field"><label class="mh-label">Team Hours</label><input class="mh-input" type="number" name="team_hours" min="0" value="0"></div><div class="mh-field"><label class="mh-label">Team Minutes</label><input class="mh-input" type="number" name="team_minutes" min="0" max="59" value="0"></div></div>
<div class="mh-row"><div class="mh-field"><label class="mh-label">Owner Hours</label><input class="mh-input" type="number" name="owner_hours" min="0" value="0"></div><div class="mh-field"><label class="mh-label">Owner Minutes</label><input class="mh-input" type="number" name="owner_minutes" min="0" max="59" value="0"></div></div>
<details><summary>Attribution Details</summary><p class="mh-muted">Need help setting up UTMs and tracking? Explore upgrades →</p></details>
<div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div><button class="mh-btn mh-btn--primary" type="submit">Save Activity</button></form></div></div>

<div class="mh-modal" id="mh-add-result" hidden><div class="mh-modal__overlay" data-mh-close-modal></div><div class="mh-modal__dialog"><div class="mh-modal__head"><h3>Add Result</h3><button type="button" data-mh-close-modal>&times;</button></div><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mh-modal__body"><input type="hidden" name="action" value="mh_add_result" /><?php wp_nonce_field('mh_add_result'); ?><input type="hidden" name="mh_range" value="<?php echo esc_attr((string) ($rangeArgs['mh_range'] ?? 'wtd')); ?>" /><input type="hidden" name="mh_from" value="<?php echo esc_attr((string) ($rangeArgs['mh_from'] ?? '')); ?>" /><input type="hidden" name="mh_to" value="<?php echo esc_attr((string) ($rangeArgs['mh_to'] ?? '')); ?>" />
<div class="mh-field"><label class="mh-label">Campaign<br><small class="mh-muted">utm_campaign</small></label><select class="mh-select" name="campaign_id"><?php foreach ($campaigns as $campaign) : ?><option value="<?php echo esc_attr((string) $campaign['id']); ?>"><?php echo esc_html((string) $campaign['name']); ?></option><?php endforeach; ?></select></div>
<div class="mh-field"><label class="mh-label">Result Category</label><select class="mh-select" name="result_category_id"><?php foreach ($resultCategories as $category) : ?><option value="<?php echo esc_attr((string) $category['id']); ?>"><?php echo esc_html((string) $category['name']); ?></option><?php endforeach; ?></select></div>
<div class="mh-field"><label class="mh-label">Channel (optional)<br><small class="mh-muted">utm_medium</small></label><select class="mh-select" name="channel_id"><option value="">Uncategorized</option><?php foreach ($channels as $group => $rows) : ?><optgroup label="<?php echo esc_attr(ucfirst($group)); ?>"><?php foreach ($rows as $channel) : ?><option value="<?php echo esc_attr((string) $channel['id']); ?>"><?php echo esc_html((string) $channel['name']); ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
<div class="mh-field"><label class="mh-label">Source<br><small class="mh-muted">utm_source</small></label><input class="mh-input" type="text" name="source"></div>
<div class="mh-field"><label class="mh-label">Content / Creative<br><small class="mh-muted">utm_content</small></label><input class="mh-input" type="text" name="meta_content"></div>
<div class="mh-field"><label class="mh-label">Term / Keyword<br><small class="mh-muted">utm_term</small></label><input class="mh-input" type="text" name="meta_term"></div>
<div class="mh-field"><label class="mh-label">Occurred date/time</label><input class="mh-input" type="datetime-local" name="occurred_at" required></div>
<div class="mh-field"><label class="mh-label">Value ($)</label><input class="mh-input" type="text" name="value"></div>
<details><summary>Attribution Details</summary><p class="mh-muted">Need help setting up UTMs and tracking? Explore upgrades →</p></details>
<div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div><button class="mh-btn mh-btn--primary" type="submit">Save Result</button></form></div></div>
