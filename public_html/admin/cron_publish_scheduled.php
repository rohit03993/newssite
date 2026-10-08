<?php
/**
 * Publish news whose go-live time has passed (India time).
 *
 * Hostinger clock job, every minute, as the site user:
 *   /usr/bin/php /home/u714405070/domains/khabarsetutv.com/public_html/admin/cron_publish_scheduled.php
 *
 * Or the same job as a web address:
 *   https://khabarsetutv.com/admin/cron_publish_scheduled.php
 *
 * The public site also publishes due stories when a page opens,
 * so a missed clock run does not leave the story hidden.
 */
@ini_set('memory_limit', '128M');
@set_time_limit(60);

$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
if (!$isCli) {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../nm/schedule.php';

if (!isset($con) || !($con instanceof mysqli)) {
    echo "Could not connect\n";
    exit(1);
}

date_default_timezone_set('Asia/Kolkata');
$now = date('Y-m-d H:i:s');
$published = nm_publish_due_scheduled($con, true);

$pushFile = __DIR__ . '/push_news.php';
$done = 0;
foreach ($published as $row) {
    $id = (int) $row['newsid'];
    $done++;
    if (is_file($pushFile)) {
        include_once $pushFile;
        if (function_exists('naradmuni_send_news_push')) {
            @naradmuni_send_news_push(
                $con,
                (string) $row['title'],
                (string) $row['short_description'],
                (string) $row['image'],
                (string) $row['newsurl']
            );
        }
    }
    echo "Published news #$id (" . $row['newsurl'] . ") scheduled for " . $row['pub_date_time'] . "\n";
}

echo "Done. Published $done scheduled item(s) at $now\n";
exit(0);
