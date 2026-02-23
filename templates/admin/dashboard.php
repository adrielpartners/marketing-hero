<?php
/** @var MarketingHero\Services\DateRange $range */
/** @var array $kpis */
/** @var array $inputs */
/** @var array $outputs */

use MarketingHero\Support\Helpers;
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header">
      <div>
        <h1 class="mh-title"><?php echo esc_html__('Marketing Hero Dashboard', 'marketing-hero'); ?></h1>
        <p class="mh-subtitle"><?php echo esc_html($range->label()); ?></p>
      </div>
    </div>

    <div class="mh-kpis">
      <div class="mh-kpi"><p class="mh-kpi__label">Leads</p><p class="mh-kpi__value"><?php echo esc_html((string) $kpis['leads']); ?></p></div>
      <div class="mh-kpi"><p class="mh-kpi__label">Booked</p><p class="mh-kpi__value"><?php echo esc_html((string) $kpis['booked']); ?></p></div>
      <div class="mh-kpi"><p class="mh-kpi__label">Sales</p><p class="mh-kpi__value"><?php echo esc_html((string) $kpis['sales_count']); ?></p></div>
      <div class="mh-kpi"><p class="mh-kpi__label">Avg Order Value</p><p class="mh-kpi__value"><?php echo esc_html(Helpers::formatMoneyCents((int) $kpis['aov_cents'])); ?></p></div>
    </div>

    <div class="mh-grid mh-grid--2" style="margin-top:16px;">
      <article class="mh-card">
        <div class="mh-card__head"><h2 class="mh-card__title">Inputs</h2></div>
        <div class="mh-card__body">
          <div class="mh-list">
            <?php foreach ($inputs['totals_by_type'] as $type => $totals) : ?>
              <div class="mh-item">
                <div class="mh-item__left">
                  <p class="mh-item__title"><?php echo esc_html(ucwords(str_replace('_', ' ', (string) $type))); ?></p>
                  <p class="mh-item__meta"><?php echo esc_html('Qty: ' . (int) $totals['quantity']); ?></p>
                </div>
                <span class="mh-pill"><?php echo esc_html(Helpers::formatMoneyCents((int) $totals['spend_cents'])); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </article>

      <article class="mh-card">
        <div class="mh-card__head"><h2 class="mh-card__title">Outputs</h2></div>
        <div class="mh-card__body">
          <div class="mh-list">
            <?php foreach ($outputs['totals_by_type'] as $type => $count) : ?>
              <div class="mh-item">
                <div class="mh-item__left">
                  <p class="mh-item__title"><?php echo esc_html(ucfirst((string) $type)); ?></p>
                </div>
                <span class="mh-pill mh-pill--accent"><?php echo esc_html((string) $count); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </article>
    </div>
  </div>
</div>
