<?php
require dirname(__DIR__) . '/nm/chrome.php';

$slug = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
if ($slug === '' && isset($_GET['slug'])) {
    $slug = trim((string) $_GET['slug']);
}
$cat = nm_category_by_url($slug);
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 21;

if (!$cat) {
    http_response_code(404);
    nm_shell_open('Page not found', '');
    echo '<h1 class="cat-h1">Page not found</h1><p><a href="' . nm_h(nm_url('/')) . '">Back to home</a></p>';
    nm_shell_close();
    exit;
}

$items = nm_news_by_category($cat['id'], $page, $perPage);
$total = nm_count_news_by_category($cat['id']);
$children = nm_child_categories($cat['id']);
$pages = max(1, (int) ceil($total / $perPage));
$canonical = $cat['cat_url'] !== '' ? $cat['cat_url'] : $slug;
$title = trim((string) $cat['metat']) !== '' ? $cat['metat'] : $cat['hindi_name'];
$desc = trim((string) $cat['metad']);

nm_shell_open($title, $desc);
?>
<section class="cat-page">
  <h1 class="cat-h1"><?php echo nm_h($cat['hindi_name']); ?></h1>
  <?php if ($desc !== ''): ?><p class="cat-intro"><?php echo nm_h($desc); ?></p><?php endif; ?>
  <?php if ($children): ?>
    <div class="district">
      <details>
        <summary style="cursor:pointer;font-weight:600;margin-bottom:8px">जिला चुनें</summary>
        <div class="pills">
          <?php foreach ($children as $c): if (empty($c['cat_url'])) continue; ?>
            <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
          <?php endforeach; ?>
        </div>
      </details>
    </div>
  <?php endif; ?>
  <?php if ($items): ?>
    <div class="cat-tiles">
      <?php foreach ($items as $i => $n) {
          echo nm_cat_tile($n, $i < 3);
      } ?>
    </div>
  <?php else: ?>
    <p style="color:var(--muted)">इस श्रेणी में अभी कोई प्रकाशित समाचार नहीं है।</p>
  <?php endif; ?>
  <div class="pager">
    <?php if ($page > 1): ?>
      <a href="<?php echo nm_h(nm_url('/category/' . $canonical . '?page=' . ($page - 1))); ?>">पिछला</a>
    <?php endif; ?>
    <?php if ($page < $pages): ?>
      <a href="<?php echo nm_h(nm_url('/category/' . $canonical . '?page=' . ($page + 1))); ?>">अगला</a>
    <?php endif; ?>
  </div>
</section>
<?php
nm_shell_close();
