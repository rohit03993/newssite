<?php
/**
 * Turn due Scheduled stories into Published.
 * Uses India time. The public site and the admin list call this,
 * so a story can go live even when the clock job does not run.
 */

function nm_publish_due_scheduled($con, $force = false)
{
    static $done = false;
    if (!($con instanceof mysqli)) {
        return array();
    }
    if ($done) {
        return array();
    }

    if (!$force) {
        $stampFile = sys_get_temp_dir() . '/nm-schedule-check.txt';
        if (is_file($stampFile) && (time() - (int) filemtime($stampFile)) < 20) {
            $done = true;
            return array();
        }
        @touch($stampFile);
    }
    $done = true;

    date_default_timezone_set('Asia/Kolkata');
    $nowTs = time();
    $nowSql = mysqli_real_escape_string($con, date('Y-m-d H:i:s'));
    $oldestOk = $nowTs - (7 * 24 * 60 * 60);

    $sql = "SELECT `newsid`, `title`, `short_description`, `image`, `newsurl`, `pub_date_time`
        FROM `news`
        WHERE `status` = 'Scheduled'
          AND `pub_date_time` IS NOT NULL
          AND `pub_date_time` >= '2000-01-01'
          AND `pub_date_time` <= '$nowSql'
        ORDER BY `newsid` DESC
        LIMIT 100";
    $q = mysqli_query($con, $sql);
    if (!$q) {
        return array();
    }

    $published = array();
    while ($row = mysqli_fetch_assoc($q)) {
        $id = (int) $row['newsid'];
        if ($id <= 0) {
            continue;
        }
        $raw = trim(str_replace('T', ' ', (string) $row['pub_date_time']));
        $when = strtotime($raw);
        if ($when === false || $when < $oldestOk || $when > $nowTs) {
            continue;
        }
        $stampDate = mysqli_real_escape_string($con, date('d-m-Y', $when));
        $stampTime = mysqli_real_escape_string($con, date('H:i', $when));
        $ok = mysqli_query(
            $con,
            "UPDATE `news` SET `status`='Published', `date`='$stampDate', `time`='$stampTime' WHERE `newsid`='$id' AND `status`='Scheduled' LIMIT 1"
        );
        if (!$ok || mysqli_affected_rows($con) < 1) {
            continue;
        }
        $published[] = $row;
    }
    return $published;
}
