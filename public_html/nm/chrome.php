<?php
require_once __DIR__ . '/lists.php';
require_once __DIR__ . '/view.php';

function nm_shell_data()
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $brand = nm_settings(array('brand_logo', 'brand_favicon', 'social_facebook', 'social_x', 'social_youtube', 'social_whatsapp'));
    $nameRow = nm_settings(array('site_title', 'site_description', 'brand_accent'));
    $siteTitle = !empty($nameRow['site_title']) ? $nameRow['site_title'] : 'The Naradmuni';
    $siteDescription = isset($nameRow['site_description']) ? $nameRow['site_description'] : 'हिंदी न्यूज़ मध्य प्रदेश';
    $accentHex = nm_accent_hex(isset($nameRow['brand_accent']) ? $nameRow['brand_accent'] : 'red');
    $cities = nm_districts_with_news();
    $data = array(
        'brand' => $brand,
        'logo' => nm_logo_src($brand),
        'favicon' => nm_favicon_src($brand),
        'siteTitle' => $siteTitle,
        'siteDescription' => $siteDescription,
        'accentHex' => $accentHex,
        'nav' => nm_main_categories(),
        'cities' => $cities,
        'topCities' => array_slice($cities, 0, 24),
        'popular' => array_slice($cities, 0, 12),
        'pages' => nm_pages(),
        'socials' => array(
            'facebook' => !empty($brand['social_facebook']) ? $brand['social_facebook'] : 'https://www.facebook.com/The-Naradmuni-100115665387257',
            'x' => !empty($brand['social_x']) ? $brand['social_x'] : 'https://twitter.com/the_naradmuni',
            'youtube' => !empty($brand['social_youtube']) ? $brand['social_youtube'] : 'https://www.youtube.com/channel/UCFk1xW3Qt_THywQF-rtO9LQ',
            'whatsapp' => !empty($brand['social_whatsapp']) ? $brand['social_whatsapp'] : 'https://api.whatsapp.com/send?phone=+917415716541',
        ),
    );
    return $data;
}

function nm_shell_open($title, $description)
{
    $d = nm_shell_data();
    $logo = $d['logo'];
    $siteTitle = $d['siteTitle'];
    $nav = $d['nav'];
    $cities = $d['cities'];
    $topCities = $d['topCities'];
    $popular = $d['popular'];
    $pageTitle = $title !== '' ? $title . ' | ' . $siteTitle : $siteTitle;
    $pageDesc = $description !== '' ? $description : $d['siteDescription'];
    ?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo nm_h($pageTitle); ?></title>
  <link rel="icon" href="<?php echo nm_h($d['favicon']); ?>">
  <meta name="description" content="<?php echo nm_h($pageDesc); ?>">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo nm_h(nm_url('/assets/site.css')); ?>?v=4">
  <style>
    :root { --accent: <?php echo nm_h($d['accentHex']); ?>; --on-accent: <?php echo nm_h(nm_accent_ink($d['accentHex'])); ?>; }
    body { font-family: "Noto Sans Devanagari", system-ui, sans-serif; }
    [hidden] { display: none !important; }
  </style>
</head>
<body class="nm-nocopy">
  <div class="site-chrome">
    <header class="header">
      <div class="header-inner">
        <div class="header-left">
          <button type="button" class="hamburger" id="open-menu" aria-label="मेनू खोलें">
            <span></span><span></span><span></span>
          </button>
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
  <div class="mnav-backdrop" id="mnav-backdrop" hidden></div>
  <aside class="mnav" id="mnav" hidden role="dialog" aria-label="मेनू">
    <div class="mnav-head">
      <a href="<?php echo nm_h(nm_url('/')); ?>" class="mnav-brand"><img src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>"></a>
      <button type="button" class="mnav-close" id="close-menu" aria-label="मेनू बंद करें">✕</button>
    </div>
    <button type="button" class="mnav-city" data-open-city>
      <span class="mnav-city-ico" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg></span>
      <span><strong>शहर चुनें</strong><small>अपने जिले की खबरें देखें</small></span>
      <span class="mnav-chev">›</span>
    </button>
    <a class="mnav-install" href="<?php echo nm_h(nm_url('/install')); ?>">
      <span><strong>ऐप इंस्टॉल करें</strong><small>Install <?php echo nm_h($siteTitle); ?></small></span>
    </a>
    <nav class="mnav-links">
      <a href="<?php echo nm_h(nm_url('/')); ?>"><span>होम</span><span class="mnav-chev">›</span></a>
      <?php foreach ($nav as $c): if (empty($c['cat_url'])) continue; ?>
        <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><span><?php echo nm_h($c['hindi_name']); ?></span><span class="mnav-chev">›</span></a>
      <?php endforeach; ?>
      <a href="<?php echo nm_h(nm_url('/latest')); ?>"><span>ताज़ा समाचार</span><span class="mnav-chev">›</span></a>
    </nav>
    <?php if ($popular): ?>
      <p class="mnav-label">लोकप्रिय शहर</p>
      <div class="mnav-chips">
        <?php foreach ($popular as $c): if (empty($c['cat_url'])) continue; ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>
  <div class="modal" id="city-modal" hidden role="dialog" aria-label="शहर चुनें">
    <div class="modal-box">
      <div class="modal-head">
        <h2>शहर चुनें</h2>
        <button type="button" class="modal-close" id="close-city" aria-label="बंद करें">✕</button>
      </div>
      <div class="modal-tools">
        <input id="city-search" class="modal-search" placeholder="जिले का नाम खोजें">
      </div>
      <div class="modal-body">
        <?php
        $groups = array();
        foreach ($cities as $c) {
            $letter = strtoupper(substr((string) (isset($c['latter']) && $c['latter'] !== '' ? $c['latter'] : $c['hindi_name']), 0, 1));
            if ($letter === '') {
                $letter = '#';
            }
            $groups[$letter][] = $c;
        }
        ksort($groups);
        foreach ($groups as $letter => $list):
        ?>
          <div class="city-group">
            <div class="city-letter"><?php echo nm_h($letter); ?></div>
            <div class="city-grid">
              <?php foreach ($list as $c): if (empty($c['cat_url'])) continue; ?>
                <a data-city="<?php echo nm_h($c['hindi_name']); ?>" data-url="<?php echo nm_h($c['cat_url']); ?>" href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="layout">
    <div>
    <?php
}

function nm_shell_close()
{
    $d = nm_shell_data();
    $logo = $d['logo'];
    $siteTitle = $d['siteTitle'];
    $pages = $d['pages'];
    $socials = $d['socials'];
    $topCities = $d['topCities'];
    ?>
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
    <?php
}

function nm_cat_tile($item, $eager)
{
    $href = nm_url('/news/' . $item['newsurl']);
    $src = nm_news_src(isset($item['image']) ? $item['image'] : '');
    $title = nm_title_html($item['title']);
    $img = $src
        ? '<img src="' . nm_h($src) . '" alt="" loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async">'
        : '<div class="ph cat-tile-ph"></div>';
    $date = !empty($item['date']) ? '<time class="news-date">' . nm_h($item['date']) . '</time>' : '';
    return '<a class="cat-tile" href="' . nm_h($href) . '">' . $img . '<div class="cat-tile-text"><h3>' . $title . '</h3>' . $date . '</div></a>';
}
