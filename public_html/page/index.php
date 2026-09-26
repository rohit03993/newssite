<?php
require dirname(__DIR__) . '/nm/chrome.php';
require_once dirname(__DIR__) . '/nm/article.php';

$slug = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
if ($slug === '' && isset($_GET['slug'])) {
    $slug = trim((string) $_GET['slug']);
}
$page = nm_page_by_slug($slug);
if (is_array($page) && !empty($page['ads_txt'])) {
    header('Location: ' . nm_url('/app-ads.txt'), true, 302);
    exit;
}
if (!$page) {
    http_response_code(404);
    nm_shell_open('Page not found', '');
    echo '<h1 class="h1">Page not found</h1><p><a href="' . nm_h(nm_url('/')) . '">Back to home</a></p>';
    nm_shell_close();
    exit;
}

$title = trim((string) $page['metat']) !== '' ? $page['metat'] : $page['page'];
$lead = trim((string) $page['metad']);
nm_shell_open($title, $lead);
?>
<article class="static-page">
  <div class="crumb"><a href="<?php echo nm_h(nm_url('/')); ?>">Home</a><span> / <?php echo nm_h($page['page']); ?></span></div>
  <h1 class="h1"><?php echo nm_h($page['page']); ?></h1>
  <?php if ($lead !== ''): ?><p class="static-page-lead"><?php echo nm_h($lead); ?></p><?php endif; ?>
  <div class="body"><?php echo nm_body_html($page['description']); ?></div>
</article>
<?php
nm_shell_close();
