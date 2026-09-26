<?php
require __DIR__ . '/nm/chrome.php';

$items = nm_latest_news(40);
nm_shell_open('ताजा खबरें', 'मध्य प्रदेश और छत्तीसगढ़ की ताज़ा हिंदी खबरें।');
?>
<section>
  <h1 class="cat-h1">ताजा खबरें</h1>
  <?php foreach ($items as $n) {
      echo nm_list_item($n);
  } ?>
</section>
<?php
nm_shell_close();
