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
    $siteTitle = !empty($nameRow['site_title']) ? $nameRow['site_title'] : 'News';
    $siteDescription = isset($nameRow['site_description']) ? $nameRow['site_description'] : 'हिंदी न्यूज़ मध्य प्रदेश';
    $themeKey = isset($nameRow['brand_accent']) ? $nameRow['brand_accent'] : 'red';
    $theme = nm_theme($themeKey);
    $data = array(
        'brand' => $brand,
        'logo' => nm_logo_src($brand),
        'favicon' => nm_favicon_src($brand),
        'siteTitle' => $siteTitle,
        'siteDescription' => $siteDescription,
        'accentHex' => $theme['hex'],
        'themeCss' => nm_theme_css($themeKey),
        'nav' => nm_main_categories(),
        'tree' => nm_category_tree(),
        'pages' => nm_pages(),
        'socials' => array(
            'facebook' => !empty($brand['social_facebook']) ? $brand['social_facebook'] : '',
            'x' => !empty($brand['social_x']) ? $brand['social_x'] : '',
            'youtube' => !empty($brand['social_youtube']) ? $brand['social_youtube'] : '',
            'whatsapp' => !empty($brand['social_whatsapp']) ? $brand['social_whatsapp'] : '',
        ),
    );
    return $data;
}

function nm_current_tab()
{
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $base = basename($script);
    if (strpos($script, '/category/') !== false) {
        return 'cats';
    }
    if ($base === 'latest.php') {
        return 'latest';
    }
    if ($base === 'search.php') {
        return 'search';
    }
    if ($base === 'index.php' && strpos($script, '/news/') === false && strpos($script, '/page/') === false) {
        return 'home';
    }
    return '';
}

function nm_logo_html($logo, $siteTitle)
{
    if ($logo !== '') {
        return '<img src="' . nm_h($logo) . '" alt="' . nm_h($siteTitle) . '">';
    }
    return '<span class="logo-text">' . nm_h($siteTitle) . '</span>';
}

function nm_social_links($socials)
{
    $html = '';
    if ($socials['facebook'] !== '') {
        $html .= '<a class="footer-social" href="' . nm_h($socials['facebook']) . '" target="_blank" rel="noreferrer" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg></a>';
    }
    if ($socials['x'] !== '') {
        $html .= '<a class="footer-social" href="' . nm_h($socials['x']) . '" target="_blank" rel="noreferrer" aria-label="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-6.6L5.7 22H2.6l7.3-8.3L1 2h6.7l4.6 6L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z"/></svg></a>';
    }
    if ($socials['youtube'] !== '') {
        $html .= '<a class="footer-social" href="' . nm_h($socials['youtube']) . '" target="_blank" rel="noreferrer" aria-label="YouTube"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.8 15.6V8.4L15.8 12l-6 3.6z"/></svg></a>';
    }
    if ($socials['whatsapp'] !== '') {
        $html .= '<a class="footer-social" href="' . nm_h($socials['whatsapp']) . '" target="_blank" rel="noreferrer" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.46 1.34 4.97L2 22l5.25-1.37c1.45.79 3.08 1.21 4.79 1.21h.01c5.5 0 9.96-4.46 9.96-9.95C22 6.45 17.54 2 12.04 2zm5.8 14.24c-.24.68-1.4 1.25-1.93 1.33-.5.08-1.13.11-1.82-.11-.42-.14-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.17-1.56-1.17-2.97 0-1.42.74-2.11 1-2.4.26-.28.57-.35.76-.35h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.56.16.28.71 1.17 1.52 1.89 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.7-.17 1.38z"/></svg></a>';
    }
    return $html;
}

function nm_public_head($pageTitle, $pageDesc, $extraHead = '')
{
    $d = nm_shell_data();
    $siteTitle = $d['siteTitle'];
    $title = $pageTitle !== '' ? $pageTitle : $siteTitle;
    $desc = $pageDesc !== '' ? $pageDesc : $d['siteDescription'];
    ?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo nm_h($title); ?></title>
  <?php if ($d['favicon'] !== ''): ?><link rel="icon" href="<?php echo nm_h($d['favicon']); ?>"><?php endif; ?>
  <meta name="description" content="<?php echo nm_h($desc); ?>">
  <?php echo $extraHead; ?>
  <link rel="stylesheet" href="<?php echo nm_h(nm_url('/assets/site.css')); ?>?v=24">
  <style>
    <?php echo $d['themeCss']; ?>
    [hidden] { display: none !important; }
  </style>
</head>
    <?php
}

function nm_public_top()
{
    $d = nm_shell_data();
    $logo = $d['logo'];
    $siteTitle = $d['siteTitle'];
    $nav = $d['nav'];
    $picker = array();
    foreach ($d['tree'] as $branch) {
        if (empty($branch['cat_url']) || empty($branch['children'])) {
            continue;
        }
        $picker[] = $branch;
    }
    $logoHtml = nm_logo_html($logo, $siteTitle);
    ?>
<body class="nm-nocopy">
  <div class="site-chrome">
    <header class="header">
      <div class="header-inner">
        <div class="header-left">
          <button type="button" class="hamburger" data-open-menu aria-label="मेनू खोलें">
            <span></span><span></span><span></span>
          </button>
          <a href="<?php echo nm_h(nm_url('/')); ?>" class="logo"><?php echo $logoHtml; ?></a>
        </div>
        <div class="header-actions">
          <a class="icon-btn" href="<?php echo nm_h(nm_url('/search')); ?>" aria-label="खोजें">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          </a>
          <?php if ($picker): ?>
          <button type="button" class="city-btn" data-open-city aria-label="श्रेणी चुनें">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
            <span class="city-btn-label">श्रेणी चुनें</span>
          </button>
          <?php endif; ?>
          <button type="button" class="icon-btn" id="notify-btn" data-site="<?php echo nm_h($siteTitle); ?>" aria-label="सूचनाएँ" title="सूचनाएँ">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0"/></svg>
          </button>
        </div>
      </div>
    </header>
    <nav class="nav" aria-label="Main">
      <div class="nav-inner">
        <a href="<?php echo nm_h(nm_url('/')); ?>"<?php echo nm_current_tab() === 'home' ? ' class="active"' : ''; ?> aria-label="Home">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 3 12h2v8h6v-6h2v6h6v-8h2z"/></svg>
        </a>
        <?php
        foreach ($nav as $c):
          if (empty($c['cat_url']) || !nm_is_top_category($c)) continue;
        ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
      </div>
    </nav>
  </div>
  <div class="mnav-backdrop" id="mnav-backdrop" hidden></div>
  <aside class="mnav" id="mnav" hidden role="dialog" aria-label="मेनू">
    <div class="mnav-head">
      <a href="<?php echo nm_h(nm_url('/')); ?>" class="mnav-brand"><?php echo $logoHtml; ?></a>
      <button type="button" class="mnav-close" id="close-menu" aria-label="मेनू बंद करें">✕</button>
    </div>
    <?php if ($picker): ?>
    <button type="button" class="mnav-city" data-open-city>
      <span class="mnav-city-ico" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg></span>
      <span><strong>श्रेणी चुनें</strong><small>अपनी श्रेणी की खबरें देखें</small></span>
      <span class="mnav-chev">›</span>
    </button>
    <?php endif; ?>
    <a class="mnav-install" href="<?php echo nm_h(nm_url('/install')); ?>">
      <span><strong>ऐप इंस्टॉल करें</strong><small>Install <?php echo nm_h($siteTitle); ?></small></span>
    </a>
    <nav class="mnav-links">
      <a href="<?php echo nm_h(nm_url('/')); ?>"><span>होम</span><span class="mnav-chev">›</span></a>
      <a href="<?php echo nm_h(nm_url('/latest')); ?>"><span>ताज़ा समाचार</span><span class="mnav-chev">›</span></a>
      <a href="<?php echo nm_h(nm_url('/search')); ?>"><span>खोजें</span><span class="mnav-chev">›</span></a>
      <?php foreach ($nav as $c): if (empty($c['cat_url']) || !nm_is_top_category($c)) continue; ?>
        <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><span><?php echo nm_h($c['hindi_name']); ?></span><span class="mnav-chev">›</span></a>
      <?php endforeach; ?>
    </nav>
    <?php
    $drawerSubs = array();
    foreach ($d['tree'] as $branch) {
        if (empty($branch['children'])) {
            continue;
        }
        foreach ($branch['children'] as $sub) {
            $drawerSubs[] = $sub;
            if (count($drawerSubs) >= 12) {
                break 2;
            }
        }
    }
    if ($drawerSubs):
    ?>
      <p class="mnav-label">उप श्रेणियाँ</p>
      <div class="mnav-chips">
        <?php foreach ($drawerSubs as $c): if (empty($c['cat_url'])) continue; ?>
          <a href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>
  <?php if ($picker): ?>
  <div class="modal" id="city-modal" hidden role="dialog" aria-label="श्रेणी चुनें">
    <div class="modal-box">
      <div class="modal-head">
        <h2>श्रेणी चुनें</h2>
        <button type="button" class="modal-close" id="close-city" aria-label="बंद करें">✕</button>
      </div>
      <div class="modal-tools">
        <input id="city-search" class="modal-search" placeholder="श्रेणी का नाम खोजें">
      </div>
      <div class="modal-body">
        <?php foreach ($picker as $branch): ?>
          <div class="city-group" data-city-group>
            <div class="city-letter"><a href="<?php echo nm_h(nm_url('/category/' . $branch['cat_url'])); ?>"><?php echo nm_h($branch['hindi_name']); ?></a></div>
            <div class="city-grid">
              <?php foreach ($branch['children'] as $c): if (empty($c['cat_url'])) continue; ?>
                <a data-city="<?php echo nm_h($c['hindi_name']); ?>" data-parent="<?php echo nm_h($branch['hindi_name']); ?>" data-url="<?php echo nm_h($c['cat_url']); ?>" href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>"><?php echo nm_h($c['hindi_name']); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
    <?php
}

function nm_public_rail()
{
    $d = nm_shell_data();
    $tree = $d['tree'];
    if (!$tree) {
        return;
    }
    $current = '';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (preg_match('#/category/([^/?]+)#', $uri, $match)) {
        $current = rawurldecode($match[1]);
    }
    ?>
    <aside class="rail">
      <section class="cat-rail" aria-label="श्रेणियाँ">
        <h3>श्रेणियाँ</h3>
        <ul class="cat-rail-list">
          <?php foreach ($tree as $c): if (empty($c['cat_url'])) continue;
            $kids = isset($c['children']) ? $c['children'] : array();
            $shown = array_slice($kids, 0, 8);
            $extra = count($kids) - count($shown);
            $parentOn = $current !== '' && $current === $c['cat_url'];
          ?>
            <li class="cat-rail-item">
              <a class="cat-rail-parent<?php echo $parentOn ? ' is-on' : ''; ?>" href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>">
                <span><?php echo nm_h($c['hindi_name']); ?></span>
                <span class="cat-rail-go" aria-hidden="true">›</span>
              </a>
              <?php if ($shown): ?>
                <div class="cat-rail-subs">
                  <?php foreach ($shown as $sub): if (empty($sub['cat_url'])) continue;
                    $subOn = $current !== '' && $current === $sub['cat_url'];
                  ?>
                    <a<?php echo $subOn ? ' class="is-on"' : ''; ?> href="<?php echo nm_h(nm_url('/category/' . $sub['cat_url'])); ?>"><?php echo nm_h($sub['hindi_name']); ?></a>
                  <?php endforeach; ?>
                  <?php if ($extra > 0): ?>
                    <a class="cat-rail-more" href="<?php echo nm_h(nm_url('/category/' . $c['cat_url'])); ?>">और</a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </aside>
    <?php
}

function nm_public_bottom($extra = '')
{
    $d = nm_shell_data();
    $tab = nm_current_tab();
    $logo = $d['logo'];
    $siteTitle = $d['siteTitle'];
    ?>
  <footer class="footer">
    <div class="shell">
      <?php if ($logo !== ''): ?><img class="footer-logo" src="<?php echo nm_h($logo); ?>" alt="<?php echo nm_h($siteTitle); ?>"><?php endif; ?>
      <div class="footer-links">
        <?php foreach ($d['pages'] as $p): ?>
          <a href="<?php echo nm_h(nm_url('/page/' . $p['page_url'])); ?>"><?php echo nm_h($p['page']); ?></a>
        <?php endforeach; ?>
      </div>
      <div class="footer-socials"><?php echo nm_social_links($d['socials']); ?></div>
      <p class="copy">Copyright © <?php echo date('Y'); ?> <?php echo nm_h($siteTitle); ?>. All Rights Reserved.</p>
    </div>
  </footer>
  <nav class="tabbar" aria-label="ऐप">
    <a href="<?php echo nm_h(nm_url('/')); ?>"<?php echo $tab === 'home' ? ' class="is-on"' : ''; ?>>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 3 12h2v8h6v-6h2v6h6v-8h2z"/></svg>
      <span>होम</span>
    </a>
    <a href="<?php echo nm_h(nm_url('/latest')); ?>"<?php echo $tab === 'latest' ? ' class="is-on"' : ''; ?>>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
      <span>ताज़ा</span>
    </a>
    <button type="button" data-open-menu<?php echo $tab === 'cats' ? ' class="is-on"' : ''; ?>>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      <span>श्रेणी</span>
    </button>
    <a href="<?php echo nm_h(nm_url('/search')); ?>"<?php echo $tab === 'search' ? ' class="is-on"' : ''; ?>>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      <span>खोज</span>
    </a>
  </nav>
  <?php echo $extra; ?>
  <script src="<?php echo nm_h(nm_url('/assets/site.js')); ?>?v=4"></script>
</body>
</html>
    <?php
}

function nm_shell_open($title, $description)
{
    $d = nm_shell_data();
    $pageTitle = $title !== '' ? $title . ' | ' . $d['siteTitle'] : $d['siteTitle'] . ($d['siteDescription'] !== '' ? ' | ' . $d['siteDescription'] : '');
    $pageDesc = $description !== '' ? $description : $d['siteDescription'];
    nm_public_head($pageTitle, $pageDesc);
    nm_public_top();
    echo '<div class="layout"><div>';
}

function nm_shell_close()
{
    echo '</div>';
    nm_public_rail();
    echo '</div>';
    nm_public_bottom();
}

function nm_cat_tile($item, $eager)
{
    $href = nm_url('/news/' . $item['newsurl']);
    $src = nm_news_src(isset($item['image']) ? $item['image'] : '');
    $title = nm_title_html($item['title']);
    $img = nm_fit_media($src, nm_plain_title($item['title']), $eager);
    $date = !empty($item['date']) ? '<time class="news-date">' . nm_h($item['date']) . '</time>' : '';
    $kicker = !empty($item['hindi_name']) ? '<span class="card-kicker">' . nm_h($item['hindi_name']) . '</span>' : '';
    return '<a class="cat-tile" href="' . nm_h($href) . '">' . $img . '<div class="cat-tile-text">' . $kicker . '<h3>' . $title . '</h3>' . $date . '</div></a>';
}
