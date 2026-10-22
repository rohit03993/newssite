<?php
/**
 * First-time setup for a new client.
 * Creates empty tables when they are missing.
 * Adds five categories and one admin login only when the database has no stories and no categories.
 * An existing site is left as it is.
 */
require __DIR__ . '/admin/config.php';
require_once __DIR__ . '/admin/site_settings_lib.php';
require_once __DIR__ . '/nm/reels.php';

header('Content-Type: text/html; charset=utf-8');

function nm_setup_schema($con)
{
    $statements = array(
        "CREATE TABLE IF NOT EXISTS `admin` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `aname` varchar(500) NOT NULL,
            `aemail` varchar(500) NOT NULL,
            `apwd` varchar(500) NOT NULL,
            `image` varchar(500) DEFAULT NULL,
            `urlroot` varchar(500) DEFAULT NULL,
            `role` varchar(20) NOT NULL DEFAULT 'Admin',
            `team_id` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `short` int(11) DEFAULT NULL,
            `menu` varchar(500) DEFAULT NULL,
            `main_heading` varchar(500) DEFAULT NULL,
            `maincat` varchar(500) DEFAULT NULL,
            `hindi_name` varchar(500) DEFAULT NULL,
            `latter` varchar(200) DEFAULT NULL,
            `parent` varchar(250) DEFAULT NULL,
            `cat_url` varchar(500) DEFAULT NULL,
            `metat` varchar(1000) DEFAULT NULL,
            `metad` varchar(1000) DEFAULT NULL,
            `home` varchar(500) DEFAULT NULL,
            `home_short` int(11) DEFAULT NULL,
            `news_style` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `news` (
            `newsid` int(11) NOT NULL AUTO_INCREMENT,
            `newsurl` varchar(1000) DEFAULT NULL,
            `folder` varchar(500) DEFAULT NULL,
            `seolink` varchar(1000) DEFAULT NULL,
            `metat` varchar(1000) DEFAULT NULL,
            `metad` longtext DEFAULT NULL,
            `metak` varchar(250) DEFAULT NULL,
            `hashtags` longtext DEFAULT NULL,
            `title` varchar(1000) DEFAULT NULL,
            `short_description` longtext DEFAULT NULL,
            `description` longtext DEFAULT NULL,
            `videoid` varchar(500) DEFAULT NULL,
            `video_file` varchar(500) DEFAULT NULL,
            `show_home` varchar(500) DEFAULT NULL,
            `image` varchar(500) DEFAULT NULL,
            `img_abt` varchar(500) DEFAULT NULL,
            `img_source` varchar(500) DEFAULT NULL,
            `newstype` varchar(500) DEFAULT NULL,
            `category` varchar(500) DEFAULT NULL,
            `team_id` int(11) NOT NULL DEFAULT 0,
            `latest_news` varchar(500) DEFAULT NULL,
            `top_side_bar` varchar(500) DEFAULT NULL,
            `breaking` varchar(500) DEFAULT 'No',
            `slider` varchar(500) DEFAULT NULL,
            `latest_priority` int(11) DEFAULT NULL,
            `slider_priority` int(11) DEFAULT NULL,
            `date` varchar(500) DEFAULT NULL,
            `time` varchar(500) DEFAULT NULL,
            `status` varchar(500) DEFAULT NULL,
            `pub_date_time` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`newsid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `news_cat` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category` int(11) NOT NULL DEFAULT 0,
            `news_id` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `news_views` (
            `count_id` int(11) NOT NULL AUTO_INCREMENT,
            `datetime` datetime NOT NULL DEFAULT current_timestamp(),
            `newsid` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`count_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `pages` (
            `p_id` int(11) NOT NULL AUTO_INCREMENT,
            `page` varchar(500) DEFAULT NULL,
            `description` longtext DEFAULT NULL,
            `page_url` varchar(500) DEFAULT NULL,
            `metat` varchar(500) DEFAULT NULL,
            `metad` varchar(500) DEFAULT NULL,
            `metak` varchar(500) DEFAULT NULL,
            PRIMARY KEY (`p_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `team` (
            `t_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(500) DEFAULT NULL,
            `email` longtext DEFAULT NULL,
            `designation` varchar(500) DEFAULT NULL,
            `image` varchar(500) DEFAULT NULL,
            `fb_link` varchar(500) DEFAULT NULL,
            `tw_link` varchar(500) DEFAULT NULL,
            `short` int(11) NOT NULL DEFAULT 0,
            `date` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`t_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    );
    foreach ($statements as $sql) {
        if (!mysqli_query($con, $sql)) {
            return mysqli_error($con);
        }
    }
    nm_ensure_site_settings($con);
    nm_ensure_admin_accounts($con);
    if (function_exists('nm_ensure_news_view_base')) {
        nm_ensure_news_view_base($con);
    }
    if (function_exists('nm_fb_reels_ensure')) {
        nm_fb_reels_ensure($con);
    }
    return '';
}

function nm_setup_count($con, $sql)
{
    $q = @mysqli_query($con, $sql);
    if (!$q) {
        return -1;
    }
    $row = mysqli_fetch_assoc($q);
    return $row ? (int) $row['c'] : -1;
}

$schemaError = nm_setup_schema($con);
$newsCount = nm_setup_count($con, "SELECT COUNT(*) AS c FROM news");
$catCount = nm_setup_count($con, "SELECT COUNT(*) AS c FROM categories");
$busy = ($newsCount > 0 || $catCount > 0);
$done = false;
$error = $schemaError;

if ($schemaError === '' && !$busy && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $site = trim((string) ($_POST['site_title'] ?? ''));
    $name = trim((string) ($_POST['aname'] ?? ''));
    $email = trim((string) ($_POST['aemail'] ?? ''));
    $pass = (string) ($_POST['apwd'] ?? '');
    if ($site === '' || $name === '' || $email === '' || strlen($pass) < 6) {
        $error = 'Enter the site name, your name, email, and a password of at least 6 characters.';
    } else {
        $cats = array(
            array(1, 'Yes', 'बिग ब्रेकिंग', 'B', 'breaking', 'Breaking news'),
            array(2, 'Yes', 'स्थानीय', 'S', 'local', 'Local news'),
            array(3, 'Yes', 'हेल्थ', 'H', 'health', 'Health'),
            array(4, 'Yes', 'बिज़नेस', 'B', 'business', 'Business'),
            array(5, 'Yes', 'मनोरंजन', 'E', 'entertainment', 'Entertainment'),
        );
        $ok = true;
        foreach ($cats as $c) {
            $nameEsc = mysqli_real_escape_string($con, $c[2]);
            $urlEsc = mysqli_real_escape_string($con, $c[4]);
            $metaEsc = mysqli_real_escape_string($con, $c[5]);
            $letter = mysqli_real_escape_string($con, $c[3]);
            $sql = "INSERT INTO categories (short, menu, main_heading, maincat, hindi_name, latter, parent, cat_url, metat, metad)
                    VALUES ('" . (int) $c[0] . "', '" . $c[1] . "', 'No', '" . $nameEsc . "', '" . $nameEsc . "', '" . $letter . "', '0', '" . $urlEsc . "', '" . $metaEsc . "', '" . $metaEsc . "')";
            if (!mysqli_query($con, $sql)) {
                $ok = false;
                $error = 'Could not add the starter categories.';
                break;
            }
        }
        if ($ok) {
            nm_setting_set($con, 'site_title', $site);
            nm_setting_set($con, 'site_description', '');
            nm_setting_set($con, 'brand_accent', 'orange');
            nm_setting_set($con, 'brand_logo', '');
            nm_setting_set($con, 'brand_favicon', '');
            nm_setting_set($con, 'top_menu_ready', '1');
            $nameEsc = mysqli_real_escape_string($con, $name);
            $emailEsc = mysqli_real_escape_string($con, $email);
            $passEsc = mysqli_real_escape_string($con, $pass);
            $cols = function_exists('nm_admin_column_map') ? nm_admin_column_map($con) : array();
            if (!empty($cols['role'])) {
                $adminSql = "INSERT INTO admin (aname, aemail, apwd, role, team_id) VALUES ('$nameEsc', '$emailEsc', '$passEsc', 'Admin', 0)";
            } else {
                $adminSql = "INSERT INTO admin (aname, aemail, apwd) VALUES ('$nameEsc', '$emailEsc', '$passEsc')";
            }
            if (!mysqli_query($con, $adminSql)) {
                $error = 'Categories were added, but the admin login could not be created.';
            } else {
                $done = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>First-time setup</title>
  <style>
    body { margin: 0; font-family: Segoe UI, sans-serif; background: #f4f6f8; color: #111; }
    .card { max-width: 460px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 28px rgba(15,23,42,.08); }
    h1 { font-size: 22px; margin: 0 0 8px; }
    p { color: #475569; line-height: 1.5; }
    label { display: block; font-weight: 700; margin: 12px 0 4px; font-size: 14px; }
    input { width: 100%; box-sizing: border-box; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; font-size: 16px; }
    button { margin-top: 16px; width: 100%; border: 0; border-radius: 10px; background: #ea580c; color: #fff; font-weight: 800; padding: 12px; font-size: 16px; }
    .err { background: #fef2f2; color: #991b1b; border-radius: 10px; padding: 10px 12px; }
    ul { padding-left: 18px; }
  </style>
</head>
<body>
  <div class="card">
    <?php if ($busy) { ?>
      <h1>Setup is closed</h1>
      <p>This database already has categories or stories. Nothing was deleted. Use the admin for this site. Open setup only after you point <code>config.php</code> at a new empty database.</p>
    <?php } elseif ($done) { ?>
      <h1>Site is ready</h1>
      <p>Five categories were added: Breaking, Local, Health, Business, and Entertainment. News, staff, pages, and videos are empty.</p>
      <p>Sign in at <a href="manage.php">manage.php</a>, then upload the logo under Branding. Delete <code>setup.php</code> from the server after this.</p>
    <?php } else { ?>
      <h1>Start a new site</h1>
      <p>Empty tables are ready. This adds five categories and one admin login. It does not copy old stories.</p>
      <?php if ($error !== '') { ?><p class="err"><?php echo htmlspecialchars($error); ?></p><?php } ?>
      <form method="post">
        <label for="site_title">Site name</label>
        <input id="site_title" name="site_title" required maxlength="80">
        <label for="aname">Your name</label>
        <input id="aname" name="aname" required maxlength="80">
        <label for="aemail">Admin email</label>
        <input id="aemail" name="aemail" type="email" required>
        <label for="apwd">Password</label>
        <input id="apwd" name="apwd" type="password" required minlength="6">
        <button type="submit">Create the blank site</button>
      </form>
      <ul>
        <li>बिग ब्रेकिंग</li>
        <li>स्थानीय</li>
        <li>हेल्थ</li>
        <li>बिज़नेस</li>
        <li>मनोरंजन</li>
      </ul>
    <?php } ?>
  </div>
</body>
</html>
