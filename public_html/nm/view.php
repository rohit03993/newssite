<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/colors.php';

function nm_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function nm_plain_title($raw)
{
    $text = preg_replace('/<br\s*\/?>/i', ' ', (string) $raw);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function nm_title_html($raw)
{
    $html = (string) $raw;
    $html = strip_tags($html, '<span>');
    $html = preg_replace('/<span\b(?![^>]*style=)[^>]*>/i', '', $html);
    $html = preg_replace('/<\/span>/i', '</span>', $html);
    if (strpos($html, '<span') === false) {
        return nm_h(nm_plain_title($raw));
    }
    return $html;
}

function nm_news_src($file)
{
    $file = trim((string) $file);
    if ($file === '') {
        return '';
    }
    return nm_url('/images/news/' . rawurlencode($file));
}

function nm_logo_src($brand)
{
    $file = isset($brand['brand_logo']) ? trim($brand['brand_logo']) : '';
    if ($file === '') {
        return '';
    }
    return nm_url('/images/logo/' . rawurlencode($file));
}

function nm_favicon_src($brand)
{
    $file = isset($brand['brand_favicon']) ? trim($brand['brand_favicon']) : '';
    if ($file === '') {
        return '';
    }
    return nm_url('/images/logo/' . rawurlencode($file));
}

function nm_card($item, $priority = false)
{
    $href = nm_url('/news/' . $item['newsurl']);
    $src = nm_news_src(isset($item['image']) ? $item['image'] : '');
    $alt = nm_h(nm_plain_title($item['title']));
    $title = nm_title_html($item['title']);
    $img = $src
        ? '<img src="' . nm_h($src) . '" alt="' . $alt . '" loading="' . ($priority ? 'eager' : 'lazy') . '" decoding="async">'
        : '<div class="ph card-ph"></div>';
    return '<a class="card" href="' . nm_h($href) . '">' . $img . '<h3>' . $title . '</h3></a>';
}

function nm_list_item($item)
{
    $href = nm_url('/news/' . $item['newsurl']);
    $src = nm_news_src(isset($item['image']) ? $item['image'] : '');
    $alt = nm_h(nm_plain_title($item['title']));
    $title = nm_title_html($item['title']);
    $img = $src
        ? '<img src="' . nm_h($src) . '" alt="' . $alt . '" loading="lazy" decoding="async">'
        : '<div class="ph list-ph"></div>';
    return '<a class="list-item" href="' . nm_h($href) . '"><h3>' . $title . '</h3>' . $img . '</a>';
}

function nm_topic_block($section, $singleOnly, $showEmpty)
{
    $cat = $section['cat'];
    $items = $section['items'];
    $feature = isset($items[0]) ? $items[0] : null;
    $side = $singleOnly ? array() : array_slice($items, 1, 3);
    $more = $singleOnly ? array() : array_slice($items, 4, 4);
    $cls = 'topic-block' . ($singleOnly ? ' topic-block--single' : '');
    $html = '<section class="' . $cls . '"><div class="section-head"><h2>' . nm_h($cat['hindi_name']) . '</h2>';
    if (!empty($cat['cat_url'])) {
        $html .= '<a class="more" href="' . nm_h(nm_url('/category/' . $cat['cat_url'])) . '">और देखें →</a>';
    }
    $html .= '</div>';

    if (!$singleOnly && !empty($section['districts'])) {
        $html .= '<div class="pills pills--tabs">';
        $districts = array_slice($section['districts'], 0, 12);
        foreach ($districts as $d) {
            if (empty($d['cat_url'])) {
                continue;
            }
            $html .= '<a href="' . nm_h(nm_url('/category/' . $d['cat_url'])) . '">' . nm_h($d['hindi_name']) . '</a>';
        }
        $html .= '</div>';
    }

    if ($feature) {
        $href = nm_h(nm_url('/news/' . $feature['newsurl']));
        $src = nm_news_src(isset($feature['image']) ? $feature['image'] : '');
        $alt = nm_h(nm_plain_title($feature['title']));
        $img = $src
            ? '<img src="' . nm_h($src) . '" alt="' . $alt . '" loading="lazy">'
            : '<div class="ph topic-feature-ph"></div>';
        $h3 = '<h3>' . nm_title_html($feature['title']) . '</h3>';
        if ($singleOnly) {
            $html .= '<a class="topic-feature topic-feature--solo" href="' . $href . '">' . $img . $h3 . '</a>';
        } else {
            $html .= '<div class="topic-split"><a class="topic-feature" href="' . $href . '">' . $img . $h3 . '</a><div class="topic-side">';
            foreach ($side as $n) {
                $html .= nm_list_item($n);
            }
            $html .= '</div></div>';
        }
    } elseif ($showEmpty) {
        $html .= '<p class="topic-empty">जल्द आ रही हैं खबरें — टीम जल्द अपडेट करेगी।</p>';
    }

    if ($more) {
        $html .= '<div class="cards cards--home cards--more">';
        foreach ($more as $n) {
            $html .= nm_card($n);
        }
        $html .= '</div>';
    }
    $html .= '</section>';
    return $html;
}

function nm_drop_old_share($text, $siteTitle)
{
    $text = trim(nm_old_brand_to_site($text, $siteTitle));
    if ($text === '') {
        return '';
    }
    $old = array(
        'chat.whatsapp.com/BkZoIpOAGBS6YFMSn2xSoM',
        'onelink.to/kqnpym',
        'thenaradmuni.com',
        'com.thenaradmuni.news',
    );
    $lower = strtolower($text);
    foreach ($old as $bit) {
        if (strpos($lower, $bit) !== false) {
            return '';
        }
    }
    return $text;
}

function nm_whatsapp_footer()
{
    if (!function_exists('nm_settings')) {
        return '';
    }
    $saved = nm_settings(array(
        'wa_share_invite_text',
        'wa_share_group_link',
        'wa_share_app_text',
        'wa_share_app_link',
    ));
    $nameRow = nm_settings(array('site_title'));
    $siteTitle = isset($nameRow['site_title']) ? $nameRow['site_title'] : '';
    $invite = nm_drop_old_share(isset($saved['wa_share_invite_text']) ? $saved['wa_share_invite_text'] : '', $siteTitle);
    $group = nm_drop_old_share(isset($saved['wa_share_group_link']) ? $saved['wa_share_group_link'] : '', $siteTitle);
    $appText = nm_drop_old_share(isset($saved['wa_share_app_text']) ? $saved['wa_share_app_text'] : '', $siteTitle);
    $appLink = nm_drop_old_share(isset($saved['wa_share_app_link']) ? $saved['wa_share_app_link'] : '', $siteTitle);
    $parts = array();
    if ($invite !== '') {
        $parts[] = $invite;
    }
    if ($group !== '') {
        $parts[] = $group;
    }
    $appBlock = $appText;
    if ($appLink !== '') {
        $appBlock = $appBlock === '' ? $appLink : $appBlock . "\n" . $appLink;
    }
    if ($appBlock !== '') {
        $parts[] = $appBlock;
    }
    return implode("\n\n", $parts);
}

function nm_share_actions($title, $url)
{
    $fb = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url);
    $x = 'https://twitter.com/intent/tweet?url=' . rawurlencode($url) . '&text=' . rawurlencode($title);
    $message = $title . "\n" . $url;
    $footer = nm_whatsapp_footer();
    if ($footer !== '') {
        $message .= "\n\n" . $footer;
    }
    $wa = 'https://wa.me/?text=' . rawurlencode($message);
    $fbIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg>';
    $xIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-6.6L5.7 22H2.6l7.3-8.3L1 2h6.7l4.6 6L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z"/></svg>';
    $waIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.46 1.34 4.97L2 22l5.25-1.37c1.45.79 3.08 1.21 4.79 1.21h.01c5.5 0 9.96-4.46 9.96-9.95C22 6.45 17.54 2 12.04 2zm5.8 14.24c-.24.68-1.4 1.25-1.93 1.33-.5.08-1.13.11-1.82-.11-.42-.14-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.17-1.56-1.17-2.97 0-1.42.74-2.11 1-2.4.26-.28.57-.35.76-.35h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.56.16.28.71 1.17 1.52 1.89 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.7-.17 1.38z"/></svg>';
    return '<div class="actions share-actions" aria-label="Share">'
        . '<a class="share-btn share-btn--fb" href="' . nm_h($fb) . '" target="_blank" rel="noreferrer" aria-label="Share on Facebook" title="Facebook">' . $fbIcon . '</a>'
        . '<a class="share-btn share-btn--x" href="' . nm_h($x) . '" target="_blank" rel="noreferrer" aria-label="Share on X" title="X">' . $xIcon . '</a>'
        . '<a class="share-btn share-btn--wa" href="' . nm_h($wa) . '" target="_blank" rel="noreferrer" aria-label="Share on WhatsApp" title="WhatsApp">' . $waIcon . '</a>'
        . '</div>';
}
