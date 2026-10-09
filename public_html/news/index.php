<?php
require dirname(__DIR__) . '/nm/article.php';
require dirname(__DIR__) . '/nm/view.php';
require dirname(__DIR__) . '/nm/reels.php';

$slug = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
$article = $slug !== '' ? nm_article($slug) : null;
$brand = nm_settings(array('brand_logo', 'brand_favicon'));
$logo = nm_logo_src($brand);
$nav = nm_main_categories();
$cities = nm_districts_with_news();
$topCities = array_slice($cities, 0, 24);
$pages = nm_pages();
$social = nm_settings(array('social_facebook', 'social_x', 'social_youtube', 'social_whatsapp'));
$socials = array(
    'facebook' => !empty($social['social_facebook']) ? $social['social_facebook'] : '',
    'x' => !empty($social['social_x']) ? $social['social_x'] : '',
    'youtube' => !empty($social['social_youtube']) ? $social['social_youtube'] : '',
    'whatsapp' => !empty($social['social_whatsapp']) ? $social['social_whatsapp'] : '',
);
$accentRow = nm_settings(array('brand_accent'));
$accentHex = nm_accent_hex(isset($accentRow['brand_accent']) ? $accentRow['brand_accent'] : 'red');
$nameRow = nm_settings(array('site_title', 'site_description'));
$siteTitle = !empty($nameRow['site_title']) ? $nameRow['site_title'] : 'News';
$siteDescription = isset($nameRow['site_description']) ? $nameRow['site_description'] : 'हिंदी न्यूज़ मध्य प्रदेश';
$headline = $article ? nm_plain_title($article['title']) : '';
$author = ($article && $article['author_name'] !== '') ? $article['author_name'] : $siteTitle;
$authorFile = ($article && $article['author_image'] !== '') ? (string) $article['author_image'] : '';
$authorPhoto = ($authorFile !== '' && !preg_match('/logo|favicon|appicon|narad/i', $authorFile))
    ? nm_url('/team/' . rawurlencode($authorFile))
    : '';
$place = ($article && !empty($article['hindi_name'])) ? $article['hindi_name'] : '';
?><!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $article ? nm_h($headline) : nm_h($siteTitle); ?> | <?php echo nm_h($siteTitle); ?></title>
  <?php if (nm_favicon_src($brand) !== ''): ?><link rel="icon" href="<?php echo nm_h(nm_favicon_src($brand)); ?>"><?php endif; ?>
  <?php
    $metaDesc = $article && !empty($article['short_description'])
        ? nm_plain_title($article['short_description'])
        : ($siteDescription !== '' ? $siteDescription : $siteTitle);
    $shareImage = '';
    $shareUrl = nm_public_origin() . '/';
    if ($article && !empty($article['newsurl'])) {
        $shareUrl = nm_public_origin() . '/news/' . rawurlencode($article['newsurl']);
    }
    $imageFile = $article && !empty($article['image']) ? basename((string) $article['image']) : '';
    if ($imageFile !== '' && preg_match('/\.(jpe?g|png|webp|gif)$/i', $imageFile)) {
        $shareImage = nm_public_origin() . '/og-image.php?f=' . rawurlencode($imageFile);
    }
  ?>
  <meta name="description" content="<?php echo nm_h($metaDesc); ?>">
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="<?php echo nm_h($siteTitle); ?>">
  <meta property="og:title" content="<?php echo nm_h($headline !== '' ? $headline : $siteTitle); ?>">
  <meta property="og:description" content="<?php echo nm_h($metaDesc); ?>">
  <meta property="og:url" content="<?php echo nm_h($shareUrl); ?>">
  <?php if ($shareImage !== ''): ?>
  <meta property="og:image" content="<?php echo nm_h($shareImage); ?>">
  <meta property="og:image:secure_url" content="<?php echo nm_h($shareImage); ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?php echo nm_h($shareImage); ?>">
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo nm_h(nm_url('/assets/site.css')); ?>?v=10">
  <style>
    :root { --accent: <?php echo nm_h($accentHex); ?>; --on-accent: <?php echo nm_h(nm_accent_ink($accentHex)); ?>; }
    body { font-family: "Noto Sans Devanagari", system-ui, sans-serif; }
    [hidden] { display: none !important; }
    .body div { margin-bottom: 0.75em !important; }
  </style>
</head>
<body class="nm-nocopy">
  <div class="site-chrome">
    <header class="header">
      <div class="header-inner">
        <div class="header-left">
          <a href="<?php echo nm_h(nm_url('/')); ?>" class="logo">
            <?php if ($logo !== ''): ?><img src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>"><?php else: ?><?php echo nm_h($siteTitle); ?><?php endif; ?>
          </a>
        </div>
        <div class="header-actions">
          <button type="button" class="city-btn" data-open-city aria-label="शहर चुनें">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
            <span class="city-btn-label">शहर चुनें</span>
          </button>
          <button type="button" class="icon-btn" id="notify-btn" data-site="<?php echo nm_h($siteTitle); ?>" aria-label="सूचनाएँ" title="सूचनाएँ">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0"/></svg>
          </button>
        </div>
      </div>
    </header>
    <nav class="nav" aria-label="Main">
      <div class="nav-inner">
        <a href="<?php echo nm_h(nm_url('/')); ?>" aria-label="Home">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 3 12h2v8h6v-6h2v6h6v-8h2z"/></svg>
        </a>
        <?php foreach ($nav as $c): if (empty($c['cat_url'])) continue; ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
        <?php foreach ($topCities as $c): if (empty($c['cat_url'])) continue; ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
      </div>
    </nav>
  </div>

  <div class="layout">
    <div>
      <?php if (!$article): ?>
        <h1 class="h1">Story not found</h1>
        <p><a href="<?php echo nm_h(nm_url('/')); ?>">Back to home</a></p>
      <?php else: ?>
        <article>
          <?php if (!empty($article['cat_url']) || !empty($article['date'])): ?>
            <div class="article-kicker">
              <?php if (!empty($article['cat_url'])): ?>
                <div class="crumb"><a href="<?php echo nm_h(nm_url('/category/' . $article['cat_url'])); ?>"><?php echo nm_h($article['hindi_name']); ?></a></div>
              <?php endif; ?>
              <?php if (!empty($article['date'])): ?>
                <time class="news-date news-date--article"><?php echo nm_h($article['date']); ?></time>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <h1 class="h1 news-title"><?php echo nm_title_html($article['title']); ?></h1>
          <div class="meta-row">
            <div class="meta-left">
              <div class="byline byline--modern">
                <div class="byline-avatar">
                  <?php if ($authorPhoto !== ''): ?>
                    <img src="<?php echo nm_h($authorPhoto); ?>" alt="">
                  <?php else: ?>
                    <span class="byline-avatar-fallback"><?php echo nm_h(function_exists('mb_substr') ? mb_substr($author, 0, 1) : substr($author, 0, 1)); ?></span>
                  <?php endif; ?>
                </div>
                <div class="byline-text">
                  <p class="byline-author"><span class="byline-by">By </span><strong><?php echo nm_h($author); ?></strong></p>
                  <p class="byline-desk"><?php echo nm_h($siteTitle); ?><?php echo $place !== '' ? ', ' . nm_h($place) : ''; ?></p>
                </div>
              </div>
            </div>
            <div class="meta-tools">
              <?php echo nm_share_actions($headline, $shareUrl); ?>
            </div>
          </div>
          <?php
            $summary = trim(strip_tags((string) $article['short_description']));
            if ($summary !== '' && $summary !== $headline):
          ?>
            <p class="summary"><?php echo nm_h($summary); ?></p>
          <?php endif; ?>
          <?php $src = nm_news_src($article['image']); if ($src): ?>
            <figure class="article-lead">
              <img src="<?php echo nm_h($src); ?>" alt="<?php echo nm_h($headline); ?>">
              <?php if (!empty($article['img_abt'])): ?><figcaption class="caption"><?php echo nm_h($article['img_abt']); ?></figcaption><?php endif; ?>
            </figure>
          <?php endif; ?>
          <div class="body"><?php echo nm_body_html($article['description']); ?></div>
          <div class="story-end">
            <span class="story-views" title="Views">
              <span class="story-views-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
              </span>
              <strong><?php echo number_format(nm_news_view_total($article['newsid']) + 1); ?></strong>
              <span class="story-views-label">बार देखा गया</span>
            </span>
          </div>
          <?php
            $trendReels = nm_fb_reels_public(nm_con());
            if ($trendReels):
          ?>
            <section class="trend" aria-label="ट्रेंडिंग">
              <div class="trend-head">
                <span class="trend-live" aria-hidden="true"></span>
                <h2>ट्रेंडिंग</h2>
              </div>
              <div class="trend-row">
                <?php foreach ($trendReels as $reel): ?>
                  <article class="trend-card">
                    <div class="trend-frame">
                      <iframe
                        src="<?php echo nm_h($reel['embed']); ?>"
                        title="<?php echo nm_h($reel['title'] !== '' ? $reel['title'] : 'Facebook video'); ?>"
                        loading="lazy"
                        scrolling="no"
                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                      <button type="button" class="trend-tap" aria-label="Play video">
                        <span aria-hidden="true">
                          <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                      </button>
                    </div>
                    <?php if (trim((string) $reel['title']) !== ''): ?>
                      <p class="trend-title"><?php echo nm_h($reel['title']); ?></p>
                    <?php endif; ?>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
          <?php if (!empty($article['related'])): ?>
            <section class="related">
              <h2>ये भी पढ़ें</h2>
              <div class="related-grid">
                <?php foreach ($article['related'] as $i => $n): $relSrc = nm_news_src(isset($n['image']) ? $n['image'] : ''); ?>
                  <a class="related-item" href="<?php echo nm_h(nm_url('/news/' . $n['newsurl'])); ?>">
                    <span class="num"><?php echo $i + 1; ?></span>
                    <div class="related-item-text">
                      <h3><?php echo nm_title_html($n['title']); ?></h3>
                      <?php if (!empty($n['date'])): ?><time class="news-date"><?php echo nm_h($n['date']); ?></time><?php endif; ?>
                    </div>
                    <?php if ($relSrc): ?><img src="<?php echo nm_h($relSrc); ?>" alt=""><?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
          <?php if ($article['author_name'] !== ''): ?>
            <section class="author-box">
              <?php if ($authorPhoto !== ''): ?>
                <img src="<?php echo nm_h($authorPhoto); ?>" alt="<?php echo nm_h($author); ?>">
              <?php else: ?>
                <div class="ph" style="width:80px;height:80px;border-radius:9999px"></div>
              <?php endif; ?>
              <div>
                <p class="author-kicker">लेखक के बारे में</p>
                <h3><?php echo nm_h($author); ?></h3>
                <?php if ($article['author_role'] !== ''): ?><p><?php echo nm_h($article['author_role']); ?></p><?php endif; ?>
                <?php if ($article['author_id']): ?>
                  <a href="<?php echo nm_h(nm_url('/author/' . $article['author_id'])); ?>">View all posts by <?php echo nm_h($author); ?> →</a>
                <?php endif; ?>
              </div>
            </section>
          <?php endif; ?>
        </article>
      <?php endif; ?>
    </div>
    <aside class="rail">
      <?php if ($topCities): ?>
        <section class="cities-rail" aria-label="शहर">
          <h3>शहर <span class="cities-rail-note">जिनमें खबरें हैं</span></h3>
          <ul class="cities-rail-list">
            <?php foreach ($topCities as $c): if (empty($c['cat_url'])) continue; ?>
              <li><a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>
    </aside>
  </div>
  <footer class="footer">
    <div class="shell">
      <?php if ($logo !== ''): ?><img src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>" style="height:48px;margin:0 auto 16px"><?php endif; ?>
      <div class="footer-links">
        <?php foreach ($pages as $p): ?>
          <a href="<?php echo nm_h(nm_url('/page/' . $p['page_url'])); ?>"><?php echo nm_h($p['page']); ?></a>
        <?php endforeach; ?>
      </div>
      <div class="footer-socials">
        <?php if ($socials['facebook'] !== ''): ?><a class="footer-social" href="<?php echo nm_h($socials['facebook']); ?>" target="_blank" rel="noreferrer" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg></a><?php endif; ?>
        <?php if ($socials['x'] !== ''): ?><a class="footer-social" href="<?php echo nm_h($socials['x']); ?>" target="_blank" rel="noreferrer" aria-label="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-6.6L5.7 22H2.6l7.3-8.3L1 2h6.7l4.6 6L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z"/></svg></a><?php endif; ?>
        <?php if ($socials['youtube'] !== ''): ?><a class="footer-social" href="<?php echo nm_h($socials['youtube']); ?>" target="_blank" rel="noreferrer" aria-label="YouTube"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.8 15.6V8.4L15.8 12l-6 3.6z"/></svg></a><?php endif; ?>
        <?php if ($socials['whatsapp'] !== ''): ?><a class="footer-social" href="<?php echo nm_h($socials['whatsapp']); ?>" target="_blank" rel="noreferrer" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.46 1.34 4.97L2 22l5.25-1.37c1.45.79 3.08 1.21 4.79 1.21h.01c5.5 0 9.96-4.46 9.96-9.95C22 6.45 17.54 2 12.04 2zm5.8 14.24c-.24.68-1.4 1.25-1.93 1.33-.5.08-1.13.11-1.82-.11-.42-.14-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.17-1.56-1.17-2.97 0-1.42.74-2.11 1-2.4.26-.28.57-.35.76-.35h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.56.16.28.71 1.17 1.52 1.89 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.7-.17 1.38z"/></svg></a><?php endif; ?>
      </div>
      <p class="copy">Copyright © <?php echo date('Y'); ?> <?php echo nm_h($siteTitle); ?>. All Rights Reserved.</p>
    </div>
  </footer>
  <script src="<?php echo nm_h(nm_url('/assets/site.js')); ?>?v=2"></script>
  <script>
    (function () {
      var row = document.querySelector(".trend-row");
      if (!row) return;
      var startX = 0, startY = 0, moved = false;
      row.addEventListener("touchstart", function (e) {
        var t = e.changedTouches[0];
        startX = t.clientX;
        startY = t.clientY;
        moved = false;
      }, { passive: true });
      row.addEventListener("touchmove", function (e) {
        var t = e.changedTouches[0];
        if (Math.abs(t.clientX - startX) > 8 || Math.abs(t.clientY - startY) > 8) moved = true;
      }, { passive: true });
      row.addEventListener("click", function (e) {
        var btn = e.target.closest ? e.target.closest(".trend-tap") : null;
        if (!btn || moved) return;
        var frame = btn.closest(".trend-frame");
        if (frame) frame.classList.add("is-on");
      });
    })();
  </script>
  <?php if ($article && !empty($article['newsid'])): ?>
  <script>
    fetch(<?php echo json_encode(nm_url('/view-hit.php')); ?>, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "newsid=<?php echo (int) $article['newsid']; ?>",
      keepalive: true
    }).catch(function () {});
  </script>
  <?php endif; ?>
</body>
</html>
