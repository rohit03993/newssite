<?php
require dirname(__DIR__) . '/nm/chrome.php';
require dirname(__DIR__) . '/nm/article.php';
require dirname(__DIR__) . '/nm/reels.php';

$slug = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
$article = $slug !== '' ? nm_article($slug) : null;
$d = nm_shell_data();
$siteTitle = $d['siteTitle'];
$siteDescription = $d['siteDescription'];
$headline = $article ? nm_plain_title($article['title']) : '';
$author = ($article && $article['author_name'] !== '') ? $article['author_name'] : $siteTitle;
$authorFile = ($article && $article['author_image'] !== '') ? (string) $article['author_image'] : '';
$authorPhoto = ($authorFile !== '' && !preg_match('/logo|favicon|appicon|narad/i', $authorFile))
    ? nm_url('/team/' . rawurlencode($authorFile))
    : '';
$place = ($article && !empty($article['hindi_name'])) ? $article['hindi_name'] : '';
$trendReels = $article ? nm_fb_reels_public(nm_con()) : array();

$metaDesc = $article && !empty($article['short_description'])
    ? nm_plain_title($article['short_description'])
    : ($siteDescription !== '' ? $siteDescription : $siteTitle);
$shareUrl = nm_public_origin() . '/';
if ($article && !empty($article['newsurl'])) {
    $shareUrl = nm_public_origin() . '/news/' . rawurlencode($article['newsurl']);
}
$shareImage = '';
$imageFile = $article && !empty($article['image']) ? basename((string) $article['image']) : '';
if ($imageFile !== '' && preg_match('/\.(jpe?g|png|webp|gif)$/i', $imageFile)) {
    $shareImage = nm_public_origin() . '/og-image.php?f=' . rawurlencode($imageFile);
}
$pageTitle = ($article ? $headline : $siteTitle) . ' | ' . $siteTitle;
$og = '<meta property="og:type" content="article">'
    . '<meta property="og:site_name" content="' . nm_h($siteTitle) . '">'
    . '<meta property="og:title" content="' . nm_h($headline !== '' ? $headline : $siteTitle) . '">'
    . '<meta property="og:description" content="' . nm_h($metaDesc) . '">'
    . '<meta property="og:url" content="' . nm_h($shareUrl) . '">';
if ($shareImage !== '') {
    $og .= '<meta property="og:image" content="' . nm_h($shareImage) . '">'
        . '<meta property="og:image:secure_url" content="' . nm_h($shareImage) . '">'
        . '<meta property="og:image:type" content="image/jpeg">'
        . '<meta property="og:image:width" content="1200">'
        . '<meta property="og:image:height" content="630">'
        . '<meta name="twitter:card" content="summary_large_image">'
        . '<meta name="twitter:image" content="' . nm_h($shareImage) . '">';
}

nm_public_head($pageTitle, $metaDesc, $og);
nm_public_top();
?>
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
              <img src="<?php echo nm_h($src); ?>" alt="<?php echo nm_h($headline); ?>" fetchpriority="high" decoding="async">
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
                    <?php if ($relSrc): ?><img src="<?php echo nm_h($relSrc); ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
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
    <?php nm_public_rail(); ?>
  </div>
<?php
$extra = '';
if ($trendReels) {
    $trendFirst = $trendReels[0];
    $trendList = array();
    foreach ($trendReels as $reel) {
        $trendList[] = array(
            'embed' => $reel['embed'],
            'title' => $reel['title'] !== '' ? $reel['title'] : 'Trending video',
        );
    }
    $nextBtn = count($trendReels) > 1
        ? '<button type="button" class="trend-float-next" id="trend-next" aria-label="Next video">›</button>'
        : '';
    $extra .= '<aside class="trend-float" id="trend-float" aria-label="Trending video">'
        . '<div class="trend-float-bar"><span class="trend-live" aria-hidden="true"></span><strong>Trending video</strong>'
        . $nextBtn
        . '<button type="button" class="trend-float-x" id="trend-close" aria-label="Close">×</button></div>'
        . '<div class="trend-frame"><iframe id="trend-video" src="' . nm_h($trendFirst['embed']) . '" title="'
        . nm_h($trendFirst['title'] !== '' ? $trendFirst['title'] : 'Trending video')
        . '" scrolling="no" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen></iframe>'
        . '<button type="button" class="trend-tap" id="trend-play" aria-label="Play video"><span aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span></button></div></aside>'
        . '<script>(function(){var box=document.getElementById("trend-float");var frame=document.querySelector("#trend-float .trend-frame");var video=document.getElementById("trend-video");var play=document.getElementById("trend-play");var closeBtn=document.getElementById("trend-close");var next=document.getElementById("trend-next");var list='
        . json_encode($trendList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ';var index=0;if(play&&frame){play.addEventListener("click",function(){frame.classList.add("is-on");});}if(closeBtn&&box){closeBtn.addEventListener("click",function(){box.style.display="none";});}if(next&&video&&list.length>1){next.addEventListener("click",function(){index=(index+1)%list.length;video.src=list[index].embed;video.title=list[index].title;if(frame)frame.classList.remove("is-on");});}})();</script>';
}
if ($article && !empty($article['newsid'])) {
    $extra .= '<script>fetch(' . json_encode(nm_url('/view-hit.php')) . ',{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"newsid=' . (int) $article['newsid'] . '",keepalive:true}).catch(function(){});</script>';
}
nm_public_bottom($extra);
