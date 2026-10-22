<?php
require __DIR__ . '/nm/chrome.php';

$home = nm_home();
$d = nm_shell_data();
nm_shell_open('', $d['siteDescription']);
?>
<?php if (!empty($home['error'])): ?>
  <div class="db-error">
    <h1>Database connection failed</h1>
    <p>Start <strong>MySQL</strong> in XAMPP, then refresh this page.</p>
    <pre><?php echo nm_h($home['error']); ?></pre>
  </div>
<?php else: ?>
  <div class="home">
    <?php if (!empty($d['nav'])): ?>
      <div class="home-chips" aria-label="श्रेणियाँ">
        <?php foreach (array_slice($d['nav'], 0, 12) as $c): if (empty($c['cat_url'])) continue; ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($home['breaking'])): ?>
      <div class="breaking" aria-label="ब्रेकिंग">
        <span class="breaking-tag">ब्रेकिंग</span>
        <div class="breaking-track">
          <?php foreach ($home['breaking'] as $n):
            if (empty($n['newsurl'])) continue;
            if (!empty($home['lead']['newsid']) && (int) $n['newsid'] === (int) $home['lead']['newsid']) continue;
          ?>
            <a href="<?php echo nm_h(nm_url('/news/' . $n['newsurl'])); ?>"><?php echo nm_title_html($n['title']); ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="hero">
      <?php if (!empty($home['lead'])): $lead = $home['lead']; $leadSrc = nm_news_src(isset($lead['image']) ? $lead['image'] : ''); ?>
        <a class="hero-lead" href="<?php echo nm_h(nm_url('/news/' . $lead['newsurl'])); ?>">
          <span class="hero-photo">
            <?php if ($leadSrc): ?>
              <img src="<?php echo nm_h($leadSrc); ?>" alt="<?php echo nm_h(nm_plain_title($lead['title'])); ?>" fetchpriority="high" decoding="async">
            <?php else: ?>
              <span class="ph hero-ph"></span>
            <?php endif; ?>
          </span>
          <span class="hero-copy">
            <?php if (!empty($lead['hindi_name'])): ?><span class="card-kicker"><?php echo nm_h($lead['hindi_name']); ?></span><?php endif; ?>
            <h2><?php echo nm_title_html($lead['title']); ?></h2>
          </span>
        </a>
      <?php else: ?>
        <p class="topic-empty">No published news found.</p>
      <?php endif; ?>
      <?php if (!empty($home['secondaries'])): ?>
        <div class="hero-side">
          <?php foreach ($home['secondaries'] as $n) echo nm_list_item($n); ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($home['trending'])): ?>
      <section class="trend-news" aria-label="ट्रेंडिंग">
        <div class="section-head">
          <h2>ट्रेंडिंग</h2>
          <a class="more" href="<?php echo nm_h(nm_url('/latest')); ?>">और देखें →</a>
        </div>
        <div class="trend-news-row">
          <?php foreach ($home['trending'] as $i => $n) echo nm_card($n, $i < 1); ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!empty($home['shorts'])): ?>
      <section class="shorts-block topic-block" aria-label="YouTube Shorts">
        <div class="section-head">
          <h2>Shorts</h2>
          <?php if (!empty($d['socials']['youtube'])): ?>
            <a class="more" href="<?php echo nm_h($d['socials']['youtube']); ?>" target="_blank" rel="noreferrer">और वीडियो देखें →</a>
          <?php endif; ?>
        </div>
        <div class="shorts-rail">
          <?php foreach ($home['shorts'] as $s): ?>
            <a class="shorts-card" href="https://www.youtube.com/shorts/<?php echo nm_h($s['id']); ?>" target="_blank" rel="noreferrer">
              <span class="shorts-thumb"><img src="<?php echo nm_h($s['thumb']); ?>" alt="" loading="lazy" decoding="async"></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="topic-block">
      <div class="section-head">
        <h2>ताज़ा खबरें</h2>
        <a class="more" href="<?php echo nm_h(nm_url('/latest')); ?>">और देखें →</a>
      </div>
      <div class="cards cards--home">
        <?php foreach ($home['grid'] as $i => $n) echo nm_card($n, $i < 2); ?>
      </div>
    </section>
  </div>
<?php endif; ?>
<?php
nm_shell_close();
