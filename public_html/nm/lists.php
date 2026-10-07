<?php
require_once __DIR__ . '/home.php';

function nm_category_by_url($slug)
{
    $slug = trim((string) $slug);
    if ($slug === '') {
        return null;
    }
    $rows = nm_rows(
        "SELECT id, hindi_name, cat_url, metad, metat, parent
         FROM categories
         WHERE LOWER(cat_url) = LOWER(?)
         LIMIT 1",
        's',
        array($slug)
    );
    return $rows ? $rows[0] : null;
}

function nm_news_page_by_category($catId, $page, $perPage)
{
    $page = max(1, (int) $page);
    $perPage = min(40, max(1, (int) $perPage));
    $offset = ($page - 1) * $perPage;
    $cat = (string) $catId;
    return nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.date, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ?
           AND (n.category = ? OR EXISTS (
             SELECT 1 FROM news_cat nc WHERE nc.news_id = n.newsid AND nc.category = ?
           ))
           AND (n.newstype IS NULL OR n.newstype != 'Video')
         ORDER BY n.newsid DESC
         LIMIT " . (int) $perPage . " OFFSET " . (int) $offset,
        'sss',
        array('Published', $cat, $cat)
    );
}

function nm_count_news_by_category($catId)
{
    $cat = (string) $catId;
    $rows = nm_rows(
        "SELECT COUNT(*) AS total
         FROM news n
         WHERE n.status = ?
           AND (n.category = ? OR EXISTS (
             SELECT 1 FROM news_cat nc WHERE nc.news_id = n.newsid AND nc.category = ?
           ))
           AND (n.newstype IS NULL OR n.newstype != 'Video')",
        'sss',
        array('Published', $cat, $cat)
    );
    return $rows ? (int) $rows[0]['total'] : 0;
}

function nm_latest_news($limit)
{
    $n = min(40, max(1, (int) $limit));
    $preferred = nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.date, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND (n.newstype IS NULL OR n.newstype != 'Video') AND n.latest_news = 'Yes'
         ORDER BY CAST(n.latest_priority AS UNSIGNED) ASC, n.newsid DESC
         LIMIT " . $n,
        's',
        array('Published')
    );
    if (count($preferred) >= $n) {
        return $preferred;
    }
    $ids = array();
    foreach ($preferred as $row) {
        $ids[] = (int) $row['newsid'];
    }
    $need = $n - count($preferred);
    $skip = $ids ? implode(',', $ids) : '0';
    $filler = nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.date, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND (n.newstype IS NULL OR n.newstype != 'Video')
           AND n.newsid NOT IN (" . $skip . ")
         ORDER BY n.newsid DESC
         LIMIT " . (int) $need,
        's',
        array('Published')
    );
    return array_merge($preferred, $filler);
}

function nm_team_by_id($id)
{
    $rows = nm_rows(
        "SELECT t_id, name, designation, image FROM team WHERE t_id = ? LIMIT 1",
        'i',
        array((int) $id)
    );
    return $rows ? $rows[0] : null;
}

function nm_news_by_author($teamId, $limit)
{
    $n = min(40, max(1, (int) $limit));
    return nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.date, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ? AND n.team_id = ? AND (n.newstype IS NULL OR n.newstype != 'Video')
         ORDER BY n.newsid DESC
         LIMIT " . $n,
        'si',
        array('Published', (int) $teamId)
    );
}

function nm_is_ads_txt_page($url, $title)
{
    $s = strtolower(str_replace('_', '-', $url . ' ' . $title));
    return (bool) preg_match('/ads[\s.-]*txt/', $s);
}

function nm_page_by_slug($slug)
{
    $key = trim(rawurldecode((string) $slug));
    if ($key === '') {
        return null;
    }
    $rows = nm_rows(
        "SELECT page, page_url, description, metat, metad FROM pages WHERE page_url = ? LIMIT 1",
        's',
        array($key)
    );
    if (!$rows) {
        return null;
    }
    if (nm_is_ads_txt_page($rows[0]['page_url'], $rows[0]['page'])) {
        return array('ads_txt' => true);
    }
    return nm_scrub_policy_page($rows[0]);
}

function nm_scrub_policy_page($page)
{
    $slug = isset($page['page_url']) ? (string) $page['page_url'] : '';
    $policy = array('editorial-policy', 'fact-check-policy', 'correction-policy');
    if (!in_array($slug, $policy, true)) {
        return $page;
    }
    $nameRow = nm_settings(array('site_title'));
    $siteTitle = isset($nameRow['site_title']) ? $nameRow['site_title'] : '';
    $changed = false;
    foreach (array('page', 'description', 'metat', 'metad') as $field) {
        if (!isset($page[$field])) {
            continue;
        }
        $next = nm_old_brand_to_site($page[$field], $siteTitle);
        if ($next !== (string) $page[$field]) {
            $page[$field] = $next;
            $changed = true;
        }
    }
    if ($changed) {
        $con = nm_con();
        if ($con) {
            $stmt = mysqli_prepare(
                $con,
                "UPDATE pages SET page = ?, description = ?, metat = ?, metad = ? WHERE page_url = ? LIMIT 1"
            );
            if ($stmt) {
                $heading = isset($page['page']) ? (string) $page['page'] : '';
                $body = (string) $page['description'];
                $metat = isset($page['metat']) ? (string) $page['metat'] : '';
                $metad = isset($page['metad']) ? (string) $page['metad'] : '';
                mysqli_stmt_bind_param($stmt, 'sssss', $heading, $body, $metat, $metad, $slug);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    return $page;
}
