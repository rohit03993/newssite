<?php
require dirname(__DIR__) . '/nm/article.php';
require dirname(__DIR__) . '/nm/view.php';

$slug = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
$article = $slug !== '' ? nm_article($slug) : null;
$brand = nm_settings(array('brand_logo'));
$logo = nm_logo_src($brand);
$nav = nm_main_categories();
$cities = nm_districts_with_news();
$topCities = array_slice($cities, 0, 24);
$pages = nm_pages();
$social = nm_settings(array('social_facebook', 'social_x', 'social_youtube', 'social_whatsapp'));
$socials = array(
    'facebook' => !empty($social['social_facebook']) ? $social['social_facebook'] : 'https://www.facebook.com/The-Naradmuni-100115665387257',
    'x' => !empty($social['social_x']) ? $social['social_x'] : 'https://twitter.com/the_naradmuni',
    'youtube' => !empty($social['social_youtube']) ? $social['social_youtube'] : 'https://www.youtube.com/channel/UCFk1xW3Qt_THywQF-rtO9LQ',
    'whatsapp' => !empty($social['social_whatsapp']) ? $social['social_whatsapp'] : 'https://api.whatsapp.com/send?phone=+917415716541',
);
$accentRow = nm_settings(array('brand_accent'));
$accentHex = nm_accent_hex(isset($accentRow['brand_accent']) ? $accentRow['brand_accent'] : 'red');
$nameRow = nm_settings(array('site_title', 'site_description'));
$siteTitle = !empty($nameRow['site_title']) ? $nameRow['site_title'] : 'The Naradmuni';
$siteDescription = isset($nameRow['site_description']) ? $nameRow['site_description'] : 'हिंदी न्यूज़ मध्य प्रदेश';
$headline = $article ? nm_plain_title($article['title']) : '';
$author = ($article && $article['author_name'] !== '') ? $article['author_name'] : $siteTitle;
$authorPhoto = ($article && $article['author_image'] !== '') ? nm_url('/team/' . rawurlencode($article['author_image'])) : '';
$place = ($article && !empty($article['hindi_name'])) ? $article['hindi_name'] : '';
?><!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $article ? nm_h($headline) : nm_h($siteTitle); ?> | <?php echo nm_h($siteTitle); ?></title>
  <meta name="description" content="<?php echo nm_h($article && !empty($article['short_description']) ? $article['short_description'] : ($siteDescription !== '' ? $siteDescription : $siteTitle)); ?>">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo nm_h(nm_url('/assets/site.css')); ?>?v=4">
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
            <img src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>">
          </a>
        </div>
        <div class="header-actions">
          <button type="button" class="city-btn" data-open-city aria-label="शहर चुनें">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
            <span class="city-btn-label">शहर चुनें</span>
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
          <?php if (!empty($article['cat_url'])): ?>
            <div class="crumb"><a href="<?php echo nm_h(nm_url('/category/' . $article['cat_url'])); ?>"><?php echo nm_h($article['hindi_name']); ?></a></div>
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
              <?php if (!empty($article['date'])): ?>
                <time class="news-date news-date--article"><?php echo nm_h($article['date']); ?></time>
              <?php endif; ?>
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
      <img src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>" style="height:48px;margin:0 auto 16px">
      <div class="footer-links">
        <?php foreach ($pages as $p): ?>
          <a href="<?php echo nm_h(nm_url('/page/' . $p['page_url'])); ?>"><?php echo nm_h($p['page']); ?></a>
        <?php endforeach; ?>
      </div>
      <div class="footer-socials">
        <a class="footer-social" href="<?php echo nm_h($socials['facebook']); ?>" target="_blank" rel="noreferrer" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg></a>
        <a class="footer-social" href="<?php echo nm_h($socials['x']); ?>" target="_blank" rel="noreferrer" aria-label="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-6.6L5.7 22H2.6l7.3-8.3L1 2h6.7l4.6 6L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z"/></svg></a>
        <a class="footer-social" href="<?php echo nm_h($socials['youtube']); ?>" target="_blank" rel="noreferrer" aria-label="YouTube"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.8 15.6V8.4L15.8 12l-6 3.6z"/></svg></a>
        <a class="footer-social" href="<?php echo nm_h($socials['whatsapp']); ?>" target="_blank" rel="noreferrer" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.46 1.34 4.97L2 22l5.25-1.37c1.45.79 3.08 1.21 4.79 1.21h.01c5.5 0 9.96-4.46 9.96-9.95C22 6.45 17.54 2 12.04 2zm5.8 14.24c-.24.68-1.4 1.25-1.93 1.33-.5.08-1.13.11-1.82-.11-.42-.14-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.17-1.56-1.17-2.97 0-1.42.74-2.11 1-2.4.26-.28.57-.35.76-.35h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.56.16.28.71 1.17 1.52 1.89 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.7-.17 1.38z"/></svg></a>
      </div>
      <p class="copy">Copyright © <?php echo date('Y'); ?> <?php echo nm_h($siteTitle); ?>. All Rights Reserved.</p>
    </div>
  </footer>
  <script src="<?php echo nm_h(nm_url('/assets/site.js')); ?>"></script>
</body>
</html>
