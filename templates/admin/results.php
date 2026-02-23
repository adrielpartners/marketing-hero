<?php
/** @var MarketingHero\Services\DateRange $range */
/** @var array $results */
/** @var array $campaigns */
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header"><div><h1 class="mh-title">Results</h1><p class="mh-subtitle"><?php echo esc_html($range->label()); ?></p></div></div>

    <?php if (isset($_GET['mh_notice'])) : ?>
      <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sanitize_text_field((string) $_GET['mh_notice'])); ?></p></div>
    <?php endif; ?>

    <div class="mh-grid mh-grid--2">
      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Result</h2></div><div class="mh-card__body">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="mh_add_result" />
          <?php wp_nonce_field('mh_add_result'); ?>
          <div class="mh-field"><label class="mh-label">Occurred Date</label><input class="mh-input" type="date" name="occurred_at" required></div>
          <div class="mh-field"><label class="mh-label">Type</label><select class="mh-select" name="type"><option value="lead">Lead</option><option value="booked">Booked</option><option value="sale">Sale</option></select></div>
          <div class="mh-field"><label class="mh-label">Value (cents)</label><input class="mh-input" type="number" min="0" name="value_cents"></div>
          <div class="mh-field"><label class="mh-label">Campaign</label><select class="mh-select" name="campaign_id"><option value="">None</option><?php foreach ($campaigns as $campaign) : ?><option value="<?php echo esc_attr((string) $campaign['id']); ?>"><?php echo esc_html((string) $campaign['name']); ?></option><?php endforeach; ?></select></div>
          <div class="mh-field"><label class="mh-label">Source</label><input class="mh-input" type="text" name="source"></div>
          <div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div>
          <button class="mh-btn mh-btn--primary" type="submit">Add Result</button>
        </form>
      </div></article>

      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Recent Results</h2></div><div class="mh-card__body"><div class="mh-list">
        <?php foreach ($results as $result) : ?>
          <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html(ucfirst((string) $result['type'])); ?></p><p class="mh-item__meta"><?php echo esc_html((string) $result['occurred_at']); ?></p></div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
              <input type="hidden" name="action" value="mh_delete_result" />
              <input type="hidden" name="id" value="<?php echo esc_attr((string) $result['id']); ?>" />
              <?php wp_nonce_field('mh_delete_result'); ?>
              <button class="mh-btn" data-mh-confirm="Delete this result?" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div></div></article>
    </div>
  </div>
</div>
