<?php
require __DIR__ . '/nm/chrome.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$team = $id > 0 ? nm_team_by_id($id) : null;
if (!$team) {
    http_response_code(404);
    nm_shell_open('Page not found', '');
    echo '<h1 class="cat-h1">Page not found</h1><p><a href="' . nm_h(nm_url('/')) . '">Back to home</a></p>';
    nm_shell_close();
    exit;
}

$items = nm_news_by_author($team['t_id'], 30);
$photo = !empty($team['image']) ? nm_url('/team/' . rawurlencode($team['image'])) : '';
$desc = $team['designation'] !== '' ? $team['name'] . ' — ' . $team['designation'] : $team['name'];
nm_shell_open($team['name'], $desc);
?>
<section class="author-page">
  <section class="author-box">
    <?php if ($photo !== ''): ?>
      <img src="<?php echo nm_h($photo); ?>" alt="<?php echo nm_h($team['name']); ?>">
    <?php else: ?>
      <div class="ph" style="width:80px;height:80px;border-radius:9999px"></div>
    <?php endif; ?>
    <div>
      <p class="author-kicker">लेखक के बारे में</p>
      <h3><?php echo nm_h($team['name']); ?></h3>
      <?php if ($team['designation'] !== ''): ?><p><?php echo nm_h($team['designation']); ?></p><?php endif; ?>
    </div>
  </section>
  <?php if ($items): ?>
    <div class="section-head"><h2>सभी खबरें</h2></div>
    <div class="cards cards--home">
      <?php foreach ($items as $i => $n) {
          echo nm_card($n, $i < 4);
      } ?>
    </div>
  <?php endif; ?>
</section>
<?php
nm_shell_close();
