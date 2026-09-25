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
        $file = 'Logo @2x.png';
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
