<?php
require_once __DIR__ . '/db.php';

function nm_settings($keys)
{
    if (!$keys) {
        return array();
    }
    $place = implode(',', array_fill(0, count($keys), '?'));
    $types = str_repeat('s', count($keys));
    $rows = nm_rows(
        "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ($place)",
        $types,
        $keys
    );
    $out = array();
    foreach ($rows as $row) {
        $out[$row['setting_key']] = $row['setting_value'];
    }
    return $out;
}

function nm_state_parent_ids()
{
    $rows = nm_rows(
        "SELECT id FROM categories
         WHERE LOWER(cat_url) IN ('madhya-pradesh', 'chhattisgarh')
           AND (parent IS NULL OR parent = '' OR parent = '0')"
    );
    $ids = array();
    foreach ($rows as $row) {
        $ids[] = (string) $row['id'];
    }
    return $ids;
}

function nm_top_categories()
{
    return nm_rows(
        "SELECT id, hindi_name, cat_url, metad, metat, parent, menu, short, latter, main_heading
         FROM categories
         WHERE cat_url IS NOT NULL AND cat_url != ''
           AND hindi_name IS NOT NULL AND hindi_name != ''
           AND (parent IS NULL OR parent = '' OR parent = '0')
         ORDER BY short ASC, id ASC"
    );
}

function nm_is_breaking($c)
{
    $url = isset($c['cat_url']) ? $c['cat_url'] : '';
    $name = isset($c['hindi_name']) ? $c['hindi_name'] : '';
    return preg_match('/breaking/i', $url) || mb_strpos($name, 'बिग ब्रेकिंग') !== false || mb_strpos($name, 'ब्रेकिंग') !== false;
}

function nm_is_kahin($c)
{
    $url = strtolower(isset($c['cat_url']) ? $c['cat_url'] : '');
    $name = isset($c['hindi_name']) ? $c['hindi_name'] : '';
    return mb_strpos($name, 'कहिन') !== false || strpos($url, 'kahin') !== false || strpos($url, 'narad') !== false;
}

function nm_is_health($c)
{
    $url = isset($c['cat_url']) ? $c['cat_url'] : '';
    $name = isset($c['hindi_name']) ? $c['hindi_name'] : '';
    return preg_match('/health/i', $url) || mb_strpos($name, 'हेल्थ') !== false || mb_strpos($name, 'स्वास्थ्य') !== false;
}

function nm_is_entertain($c)
{
    $url = isset($c['cat_url']) ? $c['cat_url'] : '';
    $name = isset($c['hindi_name']) ? $c['hindi_name'] : '';
    return preg_match('/entertain|cinema|bollywood|filmy/i', $url) || mb_strpos($name, 'मनोरंजन') !== false || mb_strpos($name, 'सिनेमा') !== false;
}

function nm_is_business($c)
{
    $url = isset($c['cat_url']) ? $c['cat_url'] : '';
    $name = isset($c['hindi_name']) ? $c['hindi_name'] : '';
    return preg_match('/business|biz/i', $url) || mb_strpos($name, 'बिज़नेस') !== false || mb_strpos($name, 'बिजनेस') !== false || mb_strpos($name, 'व्यापार') !== false;
}

function nm_main_categories()
{
    $rows = nm_top_categories();
    $tests = array('nm_is_breaking', 'nm_is_kahin', 'nm_is_health', 'nm_is_entertain', 'nm_is_business');
    $used = array();
    $out = array();
    foreach ($tests as $test) {
        foreach ($rows as $c) {
            $id = (int) $c['id'];
            if (isset($used[$id])) {
                continue;
            }
            if ($test($c)) {
                $used[$id] = true;
                $out[] = $c;
                break;
            }
        }
    }
    return $out;
}

function nm_cards($sql, $types, $params)
{
    return nm_rows($sql, $types, $params);
}

function nm_card_sql()
{
    return "n.newsid, n.title, n.newsurl, n.image, n.short_description, n.date, n.time, c.hindi_name, c.cat_url";
}

function nm_breaking($limit)
{
    $limit = (int) $limit;
    $cols = nm_card_sql();
    return nm_cards(
        "SELECT $cols
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND (n.newstype IS NULL OR n.newstype != 'Video') AND n.latest_news = 'Yes'
         ORDER BY n.newsid DESC
         LIMIT $limit",
        's',
        array('Published')
    );
}

function nm_recent($limit)
{
    $limit = (int) $limit;
    $cols = nm_card_sql();
    return nm_cards(
        "SELECT $cols
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND (n.newstype IS NULL OR n.newstype != 'Video')
         ORDER BY n.newsid DESC
         LIMIT $limit",
        's',
        array('Published')
    );
}

function nm_pinned_lead()
{
    $settings = nm_settings(array('homepage_main_newsid'));
    $id = isset($settings['homepage_main_newsid']) ? (int) $settings['homepage_main_newsid'] : 0;
    if ($id <= 0) {
        return null;
    }
    $cols = nm_card_sql();
    $rows = nm_cards(
        "SELECT $cols
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.newsid = ? AND n.status = ? AND (n.newstype IS NULL OR n.newstype != 'Video')
         LIMIT 1",
        'is',
        array($id, 'Published')
    );
    return isset($rows[0]) ? $rows[0] : null;
}

function nm_slider_lead()
{
    $cols = nm_card_sql();
    $rows = nm_cards(
        "SELECT $cols
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND n.slider = 'Yes' AND (n.newstype IS NULL OR n.newstype != 'Video')
         ORDER BY CAST(n.slider_priority AS UNSIGNED) ASC, n.newsid DESC
         LIMIT 1",
        's',
        array('Published')
    );
    return isset($rows[0]) ? $rows[0] : null;
}

function nm_news_by_category($catId, $limit)
{
    $limit = (int) $limit;
    $cat = (string) (int) $catId;
    $cols = nm_card_sql();
    return nm_cards(
        "SELECT $cols
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ?
           AND (n.category = ? OR EXISTS (
             SELECT 1 FROM news_cat nc WHERE nc.news_id = n.newsid AND nc.category = ?
           ))
           AND (n.newstype IS NULL OR n.newstype != 'Video')
         ORDER BY n.newsid DESC
         LIMIT $limit",
        'sss',
        array('Published', $cat, $cat)
    );
}

function nm_child_categories($parentId)
{
    return nm_rows(
        "SELECT id, hindi_name, cat_url, latter
         FROM categories
         WHERE parent = ?
           AND cat_url IS NOT NULL AND cat_url != ''
           AND hindi_name IS NOT NULL AND hindi_name != ''
         ORDER BY latter ASC, hindi_name ASC",
        's',
        array((string) (int) $parentId)
    );
}

function nm_districts_with_news()
{
    $parents = nm_state_parent_ids();
    if (!$parents) {
        return array();
    }
    $place = implode(',', array_fill(0, count($parents), '?'));
    $types = 's' . str_repeat('s', count($parents));
    $params = array_merge(array('Published'), $parents);
    return nm_rows(
        "SELECT DISTINCT c.id, c.hindi_name, c.cat_url, c.latter
         FROM categories c
         INNER JOIN news n
           ON n.status = ?
          AND (n.newstype IS NULL OR n.newstype != 'Video')
          AND (n.category = CAST(c.id AS CHAR) OR n.category = c.id)
         WHERE c.parent IN ($place)
           AND c.cat_url IS NOT NULL AND c.cat_url != ''
           AND c.hindi_name IS NOT NULL AND c.hindi_name != ''
         ORDER BY c.latter ASC, c.hindi_name ASC",
        $types,
        $params
    );
}

function nm_pages()
{
    $rows = nm_rows("SELECT page, page_url FROM pages ORDER BY p_id ASC");
    $out = array();
    foreach ($rows as $row) {
        $blob = strtolower(str_replace('_', '-', $row['page_url'] . ' ' . $row['page']));
        if (preg_match('/ads[\s.-]*txt/', $blob)) {
            continue;
        }
        $out[] = $row;
    }
    return $out;
}

function nm_take_unique($pool, &$seen, $limit)
{
    $out = array();
    foreach ($pool as $item) {
        $id = isset($item['newsid']) ? (int) $item['newsid'] : 0;
        if (!$id || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $out[] = $item;
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

function nm_shorts()
{
    $settings = nm_settings(array('shorts_enabled', 'youtube_api_key', 'youtube_channel', 'shorts_count'));
    if (!isset($settings['shorts_enabled']) || $settings['shorts_enabled'] !== '1') {
        return array();
    }
    $key = isset($settings['youtube_api_key']) ? trim($settings['youtube_api_key']) : '';
    $channel = isset($settings['youtube_channel']) ? trim($settings['youtube_channel']) : '';
    if ($key === '' || $channel === '') {
        return array();
    }
    $count = isset($settings['shorts_count']) ? (int) $settings['shorts_count'] : 8;
    if ($count < 1) {
        $count = 8;
    }
    if ($count > 16) {
        $count = 16;
    }

    $channelId = '';
    if (preg_match('/^UC[\w-]{20,}$/i', $channel)) {
        $channelId = $channel;
    } elseif (preg_match('/channel\/(UC[\w-]{20,})/i', $channel, $m)) {
        $channelId = $m[1];
    } else {
        $handle = $channel;
        if (preg_match('/@([\w.-]+)/', $channel, $m)) {
            $handle = $m[1];
        }
        $handle = ltrim($handle, '@');
        $url = 'https://www.googleapis.com/youtube/v3/channels?part=id&forHandle=' . rawurlencode($handle) . '&key=' . rawurlencode($key);
        $json = nm_http_json($url);
        if ($json && isset($json['items'][0]['id'])) {
            $channelId = $json['items'][0]['id'];
        }
    }
    if ($channelId === '') {
        return array();
    }
    $search = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=' . rawurlencode($channelId)
        . '&type=video&videoDuration=short&order=date&maxResults=' . $count
        . '&key=' . rawurlencode($key);
    $data = nm_http_json($search);
    if (!$data || empty($data['items'])) {
        return array();
    }
    $out = array();
    foreach ($data['items'] as $item) {
        $id = isset($item['id']['videoId']) ? $item['id']['videoId'] : '';
        if ($id === '') {
            continue;
        }
        $title = isset($item['snippet']['title']) ? $item['snippet']['title'] : 'Short';
        $out[] = array(
            'id' => $id,
            'title' => $title,
            'thumb' => 'https://i.ytimg.com/vi/' . rawurlencode($id) . '/hqdefault.jpg',
        );
    }
    return $out;
}

function nm_http_json($url)
{
    $ctx = stream_context_create(array(
        'http' => array('timeout' => 3),
        'https' => array('timeout' => 3),
    ));
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function nm_home()
{
    if (!nm_con()) {
        return array('error' => 'Could not connect to MySQL');
    }

    $breaking = nm_breaking(6);
    $recent = nm_recent(60);
    $pinned = nm_pinned_lead();
    $mains = nm_main_categories();
    $stateIds = array_flip(nm_state_parent_ids());

    $narad = null;
    foreach ($mains as $cat) {
        if (nm_is_kahin($cat)) {
            $narad = $cat;
            break;
        }
    }
    if (!$narad) {
        $found = nm_rows(
            "SELECT id, hindi_name, cat_url FROM categories
             WHERE hindi_name LIKE ? OR LOWER(cat_url) LIKE ? OR LOWER(cat_url) LIKE ?
             ORDER BY id ASC LIMIT 1",
            'sss',
            array('%कहिन%', '%kahin%', '%narad%kahin%')
        );
        $narad = isset($found[0]) ? $found[0] : null;
    }

    $topics = array();
    foreach ($mains as $cat) {
        $items = nm_news_by_category($cat['id'], 16);
        $districts = array();
        if (isset($stateIds[(string) $cat['id']])) {
            $districts = nm_child_categories($cat['id']);
        }
        $topics[] = array('cat' => $cat, 'items' => $items, 'districts' => $districts);
    }

    $naradBlock = null;
    if ($narad) {
        $naradItems = nm_news_by_category($narad['id'], 8);
        $naradBlock = array('cat' => $narad, 'items' => $naradItems, 'fromDb' => count($naradItems));
    }

    $seen = array();
    $lead = null;
    $secondaries = array();
    if ($pinned && !empty($pinned['newsid'])) {
        $lead = $pinned;
        $seen[(int) $pinned['newsid']] = true;
        foreach ($breaking as $n) {
            $id = (int) $n['newsid'];
            if ($id && !isset($seen[$id]) && count($secondaries) < 5) {
                $seen[$id] = true;
                $secondaries[] = $n;
            }
        }
    } elseif (count($breaking) > 0) {
        $lead = $breaking[0];
        if (!empty($lead['newsid'])) {
            $seen[(int) $lead['newsid']] = true;
        }
        $rest = array_slice($breaking, 1, 5);
        foreach ($rest as $n) {
            if (!empty($n['newsid'])) {
                $seen[(int) $n['newsid']] = true;
            }
            $secondaries[] = $n;
        }
    } else {
        $lead = nm_slider_lead();
        if ($lead && !empty($lead['newsid'])) {
            $seen[(int) $lead['newsid']] = true;
        }
        $secondaries = nm_take_unique($recent, $seen, 5);
    }

    if ($naradBlock) {
        $naradBlock['items'] = nm_take_unique($naradBlock['items'], $seen, 1);
    }

    $grid = nm_take_unique($recent, $seen, 24);

    $other = array();
    foreach ($topics as $section) {
        if ($narad && (int) $section['cat']['id'] === (int) $narad['id']) {
            continue;
        }
        $fromDb = count($section['items']);
        $section['items'] = nm_take_unique($section['items'], $seen, 8);
        $section['fromDb'] = $fromDb;
        $other[] = $section;
    }

    $brand = nm_settings(array(
        'brand_logo', 'brand_favicon',
        'social_facebook', 'social_x', 'social_youtube', 'social_whatsapp',
    ));

    return array(
        'error' => '',
        'lead' => $lead,
        'secondaries' => $secondaries,
        'narad' => $naradBlock,
        'grid' => $grid,
        'other' => $other,
        'cities' => nm_districts_with_news(),
        'nav' => $mains,
        'pages' => nm_pages(),
        'shorts' => nm_shorts(),
        'brand' => $brand,
    );
}
