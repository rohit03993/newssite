<?php
/**
 * Facebook reels the admin pastes. Shown as a sideways row on a story.
 */

function nm_fb_reels_ensure($con)
{
    if (!($con instanceof mysqli)) {
        return false;
    }
    $ok = mysqli_query(
        $con,
        "CREATE TABLE IF NOT EXISTS `facebook_reels` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `reel_url` VARCHAR(500) NOT NULL,
            `title` VARCHAR(200) NOT NULL DEFAULT '',
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_on` TINYINT NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    return (bool) $ok;
}

function nm_fb_clean_url($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $raw)) {
        $raw = 'https://' . $raw;
    }
    $parts = parse_url($raw);
    if (!is_array($parts) || empty($parts['host'])) {
        return '';
    }
    $host = strtolower($parts['host']);
    $host = preg_replace('/^www\./', '', $host);
    $host = preg_replace('/^m\./', '', $host);
    $path = isset($parts['path']) ? $parts['path'] : '';
    $query = array();
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    if ($host === 'fb.watch') {
        $code = trim($path, '/');
        if ($code === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            return '';
        }
        return 'https://fb.watch/' . $code;
    }
    if ($host !== 'facebook.com' && $host !== 'fb.com') {
        return '';
    }
    if (preg_match('#/(?:reel|reels)/(\d+)#', $path, $m)) {
        return 'https://www.facebook.com/reel/' . $m[1];
    }
    if (preg_match('#/videos/(\d+)#', $path, $m)) {
        return 'https://www.facebook.com/watch/?v=' . $m[1];
    }
    if (!empty($query['v']) && preg_match('/^\d+$/', (string) $query['v'])) {
        return 'https://www.facebook.com/watch/?v=' . $query['v'];
    }
    return '';
}

function nm_fb_embed_src($url)
{
    $url = nm_fb_clean_url($url);
    if ($url === '') {
        return '';
    }
    return 'https://www.facebook.com/plugins/video.php?href='
        . rawurlencode($url)
        . '&show_text=false&width=280&height=500';
}

function nm_fb_reels_public($con)
{
    if (!($con instanceof mysqli) || !nm_fb_reels_ensure($con)) {
        return array();
    }
    $q = mysqli_query(
        $con,
        "SELECT `id`, `reel_url`, `title` FROM `facebook_reels` WHERE `is_on` = 1 ORDER BY `sort_order` ASC, `id` DESC LIMIT 12"
    );
    if (!$q) {
        return array();
    }
    $rows = array();
    while ($row = mysqli_fetch_assoc($q)) {
        $src = nm_fb_embed_src($row['reel_url']);
        if ($src === '') {
            continue;
        }
        $row['embed'] = $src;
        $rows[] = $row;
    }
    return $rows;
}
