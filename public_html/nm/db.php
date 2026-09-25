<?php
/**
 * Public site database.
 * Uses the same login details already written in admin/config.php.
 * Does not start the admin session.
 */
function nm_con()
{
    static $con = null;
    if ($con instanceof mysqli) {
        return $con;
    }

    $src = file_get_contents(dirname(__DIR__) . '/admin/config.php');
    $server = 'localhost';
    $user = '';
    $pass = '';
    $db = '';
    if (preg_match('/\$server\s*=\s*"([^"]*)"/', $src, $m)) {
        $server = $m[1];
    }
    if (preg_match('/\$user\s*=\s*"([^"]*)"/', $src, $m)) {
        $user = $m[1];
    }
    if (preg_match('/\$pass\s*=\s*"([^"]*)"/', $src, $m)) {
        $pass = $m[1];
    }
    if (preg_match('/\$db_name\s*=\s*"([^"]*)"/', $src, $m)) {
        $db = $m[1];
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $con = @mysqli_connect($server, $user, $pass, $db);
    if (!$con) {
        return null;
    }
    mysqli_set_charset($con, 'utf8mb4');
    date_default_timezone_set('Asia/Kolkata');
    return $con;
}

function nm_rows($sql, $types = '', $params = array())
{
    $con = nm_con();
    if (!$con) {
        return array();
    }
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        return array();
    }
    if ($types !== '' && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return array();
    }
    $res = mysqli_stmt_get_result($stmt);
    $rows = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function nm_base()
{
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/index.php';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    $dir = preg_replace('#/(news|category|page|author|latest|install)$#', '', $dir);
    if ($dir === '' || $dir === '/' || $dir === '.') {
        return '';
    }
    return $dir;
}

function nm_url($path)
{
    $path = '/' . ltrim($path, '/');
    return nm_base() . $path;
}
