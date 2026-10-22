<?php
require __DIR__ . '/nm/chrome.php';

$items = nm_latest_news(40);
nm_shell_open('ताजा खबरें', 'मध्य प्रदेश और छत्तीसगढ़ की ताज़ा हिंदी खबरें।');
?>
<section class="latest-page">
  <div class="section-head">
    <h1>ताजा खबरें</h1>
  </div>
  <?php if ($items): ?>
    <div class="cards cards--home">
      <?php foreach ($items as $i => $n) echo nm_card($n, $i < 3); ?>
    </div>
  <?php else: ?>
    <p class="topic-empty">अभी कोई ताज़ा खबर नहीं है।</p>
  <?php endif; ?>
</section>
<?php
nm_shell_close();
