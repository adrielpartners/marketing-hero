<?php
/** @var MarketingHero\Services\DateRange $range */
/** @var array $results */
/** @var array $campaigns */
/** @var array $resultCategories */
/** @var array $activities */

$channelsByCampaign = [];
foreach ($activities as $activity) {
    $campaignId = (int) ($activity['campaign_id'] ?? 0);
    $channelId = (int) ($activity['channel_id'] ?? 0);
    $channelName = (string) ($activity['channel_name'] ?? '');

    if ($campaignId <= 0 || $channelId <= 0 || $channelName === '') {
        continue;
    }

    $channelsByCampaign[$campaignId] ??= [];
    $channelsByCampaign[$campaignId][$channelId] = $channelName;
}
?>
<div class="wrap mh-admin">
  <div class="mh-wrap">
    <div class="mh-header"><div><h1 class="mh-title">Results (Leads, Appointments, Sales...)</h1><p class="mh-subtitle"><?php echo esc_html($range->label()); ?></p></div></div>

    <?php if (isset($_GET['mh_notice'])) : ?>
      <div class="notice notice-success is-dismissible"><p><?php echo esc_html(sanitize_text_field((string) $_GET['mh_notice'])); ?></p></div>
    <?php endif; ?>

    <div class="mh-grid mh-grid--2">
      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Add Result</h2></div><div class="mh-card__body">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="mh_add_result" />
          <?php wp_nonce_field('mh_add_result'); ?>
          <input type="hidden" name="return_page" value="marketing-hero-results" />
          <div class="mh-field"><label class="mh-label">Occurred Date</label><input class="mh-input" type="datetime-local" name="occurred_at" required></div>
          <div class="mh-field"><label class="mh-label">Type of Result</label><select class="mh-select" name="result_category_id" required><?php foreach ($resultCategories as $category) : ?><option value="<?php echo esc_attr((string) $category['id']); ?>"><?php echo esc_html((string) $category['name']); ?></option><?php endforeach; ?></select></div>
          <div class="mh-field"><label class="mh-label">Value (dollars)</label><input class="mh-input" type="text" name="value"></div>
          <div class="mh-field"><label class="mh-label">Campaign</label><select class="mh-select" name="campaign_id" id="mh-result-campaign"><option value="">None</option><?php foreach ($campaigns as $campaign) : ?><option value="<?php echo esc_attr((string) $campaign['id']); ?>"><?php echo esc_html((string) $campaign['name']); ?></option><?php endforeach; ?></select></div>
          <div class="mh-field"><label class="mh-label">Channel</label><select class="mh-select" name="channel_id" id="mh-result-channel"><option value="">Please select a campaign first.</option></select></div>
          <div class="mh-field"><label class="mh-label">Notes</label><textarea class="mh-textarea" name="notes"></textarea></div>
          <button class="mh-btn mh-btn--primary" type="submit">Add Result</button>
        </form>
      </div></article>

      <article class="mh-card"><div class="mh-card__head"><h2 class="mh-card__title">Recent Results</h2></div><div class="mh-card__body"><div class="mh-list">
        <?php foreach ($results as $result) : ?>
          <div class="mh-item"><div class="mh-item__left"><p class="mh-item__title"><?php echo esc_html(ucfirst((string) $result['result_category_name'])); ?></p><p class="mh-item__meta"><?php echo esc_html((string) $result['occurred_at']); ?></p></div>
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
<script>
(function(){
  const campaignSelect = document.getElementById('mh-result-campaign');
  const channelSelect = document.getElementById('mh-result-channel');
  const map = <?php echo wp_json_encode($channelsByCampaign); ?>;

  if (!campaignSelect || !channelSelect) {
    return;
  }

  const renderOptions = () => {
    const campaignId = campaignSelect.value;
    channelSelect.innerHTML = '';

    if (!campaignId) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'Please select a campaign first.';
      channelSelect.appendChild(option);
      return;
    }

    const channels = map[campaignId] || {};
    const entries = Object.entries(channels);

    if (entries.length === 0) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = 'No activities for this campaign yet.';
      channelSelect.appendChild(option);
      return;
    }

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select channel attribution';
    channelSelect.appendChild(defaultOption);

    entries.forEach(([id, name]) => {
      const option = document.createElement('option');
      option.value = id;
      option.textContent = name;
      channelSelect.appendChild(option);
    });
  };

  campaignSelect.addEventListener('change', renderOptions);
  renderOptions();
})();
</script>
