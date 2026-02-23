<?php
$deleteData = get_option('mh_delete_data_on_uninstall', '0');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mh_settings_nonce']) && wp_verify_nonce(sanitize_text_field((string) $_POST['mh_settings_nonce']), 'mh_save_settings') && current_user_can('manage_marketing_hero')) {
    $enabled = isset($_POST['mh_delete_data_on_uninstall']) ? '1' : '0';
    update_option('mh_delete_data_on_uninstall', $enabled);
    $deleteData = $enabled;
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'marketing-hero') . '</p></div>';
}
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header"><div><h1 class="mh-title">Settings</h1><p class="mh-subtitle">MVP settings for data lifecycle.</p></div></div>
    <article class="mh-card"><div class="mh-card__body">
      <form method="post">
        <?php wp_nonce_field('mh_save_settings', 'mh_settings_nonce'); ?>
        <label><input type="checkbox" name="mh_delete_data_on_uninstall" value="1" <?php checked('1', $deleteData); ?> />
          <?php echo esc_html__('Delete Marketing Hero data on uninstall', 'marketing-hero'); ?>
        </label>
        <p class="mh-muted">When enabled, uninstalling the plugin drops custom tables and removes plugin options.</p>
        <button class="mh-btn mh-btn--primary" type="submit">Save Settings</button>
      </form>
    </div></article>
  </div>
</div>
