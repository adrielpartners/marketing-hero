<?php
/** @var array $channels */
/** @var array $resultCategories */
/** @var int $teamRate */
/** @var int $ownerRate */
/** @var array $upgrades */
?>
<div class="wrap mh-admin"><div class="mh-wrap">
  <div class="mh-header"><div><h1 class="mh-title">Settings</h1><p class="mh-subtitle">Manage channels, result categories, costs, and upgrades.</p></div></div>

  <div class="mh-grid mh-grid--2">
    <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Time Cost Settings</h2></div><div class="mh-card__body">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="mh_save_settings" />
        <?php wp_nonce_field('mh_save_settings'); ?>
        <div class="mh-field"><label class="mh-label">Team time cost per hour (cents)</label><input class="mh-input" type="number" min="0" name="team_time_cost_per_hour_cents" value="<?php echo esc_attr((string) $teamRate); ?>" /></div>
        <div class="mh-field"><label class="mh-label">Owner time cost per hour (cents)</label><input class="mh-input" type="number" min="0" name="owner_time_cost_per_hour_cents" value="<?php echo esc_attr((string) $ownerRate); ?>" /></div>
        <button class="mh-btn mh-btn--primary" type="submit">Save Settings</button>
      </form>
    </div></article>

    <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Channel</h2></div><div class="mh-card__body">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="mh_add_channel" />
        <?php wp_nonce_field('mh_add_channel'); ?>
        <div class="mh-field"><label class="mh-label">Name</label><input class="mh-input" type="text" name="name" required /></div>
        <div class="mh-field"><label class="mh-label">Category</label><select class="mh-select" name="category"><option value="organic">Organic</option><option value="paid">Paid</option></select></div>
        <button class="mh-btn mh-btn--primary" type="submit">Add Channel</button>
      </form>
    </div></article>
  </div>

  <article class="mh-card" style="margin-top:16px;"><div class="mh-card__head"><h2 class="mh-card__title">Channels</h2></div><div class="mh-card__body"><div class="mh-list">
    <?php foreach ($channels as $group => $rows) : ?>
      <h3><?php echo esc_html(ucfirst($group)); ?></h3>
      <?php foreach ($rows as $channel) : ?>
        <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html((string) $channel['name']); ?></p><p class="mh-item__meta">Status: <?php echo !empty($channel['is_active']) ? 'Active' : 'Inactive'; ?></p></div>
          <div class="mh-row">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="mh_toggle_channel" /><input type="hidden" name="id" value="<?php echo esc_attr((string) $channel['id']); ?>" /><input type="hidden" name="is_active" value="<?php echo !empty($channel['is_active']) ? '0' : '1'; ?>" /><?php wp_nonce_field('mh_toggle_channel'); ?><button class="mh-btn" type="submit"><?php echo !empty($channel['is_active']) ? 'Deactivate' : 'Activate'; ?></button></form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="mh_delete_channel" /><input type="hidden" name="id" value="<?php echo esc_attr((string) $channel['id']); ?>" /><?php wp_nonce_field('mh_delete_channel'); ?><button class="mh-btn" data-mh-confirm="This channel may be used by historical records. Delete and reassign to Uncategorized?" type="submit">Delete</button></form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div></div></article>

  <div class="mh-grid mh-grid--2" style="margin-top:16px;">
    <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Result Category</h2></div><div class="mh-card__body"><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="mh_add_result_category" /><?php wp_nonce_field('mh_add_result_category'); ?><div class="mh-field"><label class="mh-label">Name</label><input class="mh-input" type="text" name="name" required /></div><button class="mh-btn mh-btn--primary" type="submit">Add Category</button></form></div></article>

    <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Result Categories</h2></div><div class="mh-card__body"><div class="mh-list"><?php foreach ($resultCategories as $category) : ?><div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html((string) $category['name']); ?></p></div><div class="mh-row"><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="mh_toggle_result_category" /><input type="hidden" name="id" value="<?php echo esc_attr((string) $category['id']); ?>" /><input type="hidden" name="is_active" value="<?php echo !empty($category['is_active']) ? '0' : '1'; ?>" /><?php wp_nonce_field('mh_toggle_result_category'); ?><button class="mh-btn" type="submit"><?php echo !empty($category['is_active']) ? 'Deactivate' : 'Activate'; ?></button></form><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="mh_delete_result_category" /><input type="hidden" name="id" value="<?php echo esc_attr((string) $category['id']); ?>" /><?php wp_nonce_field('mh_delete_result_category'); ?><button class="mh-btn" data-mh-confirm="Delete this category? Historical results will be reassigned to Uncategorized." type="submit">Delete</button></form></div></div><?php endforeach; ?></div></div></article>
  </div>

  <article class="mh-card" style="margin-top:16px;"><div class="mh-card__head"><h2 class="mh-card__title">Upgrades</h2></div><div class="mh-card__body"><div class="mh-list"><?php foreach ($upgrades as $upgrade) : ?><div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html((string) ($upgrade['title'] ?? 'Upgrade')); ?></p><p class="mh-item__meta"><?php echo esc_html((string) ($upgrade['description'] ?? '')); ?></p></div><a class="mh-btn mh-btn--primary" href="<?php echo esc_url((string) ($upgrade['url'] ?? '#')); ?>" <?php echo !empty($upgrade['open_in_new_tab']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html((string) ($upgrade['button_label'] ?? 'Learn More')); ?></a></div><?php endforeach; ?></div></div></article>
</div></div>
