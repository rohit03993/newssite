<?php
require_once __DIR__ . '/home.php';

function nm_article($slug)
{
    $slug = trim((string) $slug);
    if ($slug === '' || !nm_con()) {
        return null;
    }
    $rows = nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.short_description, n.description,
                n.date, n.img_abt, n.team_id, n.category, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.newsurl = ? AND n.status = ?
         LIMIT 1",
        'ss',
        array($slug, 'Published')
    );
    if (!$rows) {
        return null;
    }
    $article = $rows[0];
    $team = nm_rows(
        "SELECT t_id, name, designation, image FROM team WHERE t_id = ? LIMIT 1",
        'i',
        array((int) $article['team_id'])
    );
    $article['author_id'] = isset($team[0]['t_id']) ? (int) $team[0]['t_id'] : 0;
    $article['author_name'] = isset($team[0]['name']) ? $team[0]['name'] : '';
    $article['author_role'] = isset($team[0]['designation']) ? $team[0]['designation'] : '';
    $article['author_image'] = isset($team[0]['image']) ? $team[0]['image'] : '';
    $cat = (string) $article['category'];
    $article['related'] = nm_rows(
        "SELECT n.newsid, n.title, n.newsurl, n.image, n.date, c.hindi_name, c.cat_url
         FROM news n
         LEFT JOIN categories c ON c.id = n.category
         WHERE n.status = ?
           AND (n.category = ? OR EXISTS (
             SELECT 1 FROM news_cat nc WHERE nc.news_id = n.newsid AND nc.category = ?
           ))
           AND (n.newstype IS NULL OR n.newstype != 'Video')
           AND n.newsid != ?
         ORDER BY n.newsid DESC
         LIMIT 4",
        'sssi',
        array('Published', $cat, $cat, (int) $article['newsid'])
    );
    return $article;
}

function nm_body_html($raw)
{
    $html = (string) $raw;
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html);
    $html = str_ireplace(array('&nbsp;', '&#160;'), ' ', $html);
    $html = preg_replace('/<(?!img\b)([a-z0-9]+)([^>]*?)\sstyle=(["\']).*?\3([^>]*)>/is', '<$1$2$4>', $html);
    $html = preg_replace('/(?:<br\s*\/?>\s*){2,}/i', '</p><p>', $html);
    $html = preg_replace('/<br\s*\/?>/i', ' ', $html);
    $html = trim($html);
    if ($html !== '' && stripos($html, '<p') === false && stripos($html, '<div') === false) {
        $html = '<p>' . $html . '</p>';
    }
    return $html;
}
