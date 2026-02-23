<?php
/** @var array $campaigns */
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header"><div><h1 class="mh-title">Campaigns</h1><p class="mh-subtitle">Organize marketing efforts into named campaigns.</p></div></div>

    <?php if (isset($_GET['mh_notice'])) : ?>
      <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sanitize_text_field((string) $_GET['mh_notice'])); ?></p></div>
    <?php endif; ?>

    <div class="mh-grid mh-grid--2">
      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Campaign</h2></div><div class="mh-card__body">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="mh_add_campaign" />
          <?php wp_nonce_field('mh_add_campaign'); ?>
          <div class="mh-field"><label class="mh-label">Name</label><input class="mh-input" type="text" name="name" required></div>
          <div class="mh-field"><label class="mh-label">Start Date</label><input class="mh-input" type="date" name="start_date"></div>
          <div class="mh-field"><label class="mh-label">End Date</label><input class="mh-input" type="date" name="end_date"></div>
          <div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div>
          <button class="mh-btn mh-btn--primary" type="submit">Add Campaign</button>
        </form>
      </div></article>

      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Campaign List</h2></div><div class="mh-card__body"><div class="mh-list">
      <?php foreach ($campaigns as $campaign) : ?>
        <details class="mh-item mh-item--stack">
          <summary class="mh-item__summary">
            <div class="mh-item__left">
              <p class="mh-item__title"><?php echo esc_html((string) $campaign['name']); ?></p>
              <p class="mh-item__meta"><?php echo esc_html((string) ($campaign['start_date'] ?: '-') . ' → ' . (string) ($campaign['end_date'] ?: '-')); ?></p>
            </div>
            <span class="mh-muted">Click to view notes</span>
          </summary>
          <div class="mh-item__details">
            <p class="mh-item__meta"><?php echo esc_html((string) ($campaign['notes'] ?: 'No notes yet.')); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
              <input type="hidden" name="action" value="mh_delete_campaign" />
              <input type="hidden" name="id" value="<?php echo esc_attr((string) $campaign['id']); ?>" />
              <?php wp_nonce_field('mh_delete_campaign'); ?>
              <button class="mh-btn" data-mh-confirm="Delete this campaign?" type="submit">Delete</button>
            </form>
          </div>
        </details>
      <?php endforeach; ?>
      </div></div></article>
    </div>
  </div>
</div>
