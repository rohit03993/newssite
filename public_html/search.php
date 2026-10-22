<?php
require __DIR__ . '/nm/chrome.php';

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$items = $q !== '' ? nm_search_news($q, 30) : array();
nm_shell_open('खोजें', 'खबर खोजें');
?>
<section class="search-page">
  <h1 class="cat-h1">खोजें</h1>
  <form class="search-form" method="get" action="<?php echo nm_h(nm_url('/search')); ?>">
    <input type="search" name="q" value="<?php echo nm_h($q); ?>" placeholder="खबर का शब्द लिखें" autocomplete="off">
    <button type="submit">खोजें</button>
  </form>
  <?php if ($q === ''): ?>
    <p class="topic-empty">कम से कम दो अक्षर लिखें।</p>
  <?php elseif (!$items): ?>
    <p class="topic-empty">इस शब्द की कोई खबर नहीं मिली।</p>
  <?php else: ?>
    <div class="cards cards--home">
      <?php foreach ($items as $i => $n) echo nm_card($n, $i < 2); ?>
    </div>
  <?php endif; ?>
</section>
<?php
nm_shell_close();
