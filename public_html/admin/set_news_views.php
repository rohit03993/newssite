<?php
include "config.php";
require_once __DIR__ . "/admin_helpers.php";

if (!isset($_SESSION["aemail"])) {
	header("location: ../manage.php");
	exit;
}

nm_require_admin($con);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
	header("location: news.php");
	exit;
}

$newsid = isset($_POST["newsid"]) ? (int) $_POST["newsid"] : 0;
$viewsRaw = isset($_POST["views"]) ? preg_replace('/[^\d]/', '', (string) $_POST["views"]) : '';
$views = ($viewsRaw === '') ? -1 : (int) $viewsRaw;
if ($newsid < 1 || $views < 0 || $views > 99999999) {
	nm_js_notice("Type a view number from 0 up.", "news.php", "error");
}

if (nm_set_displayed_views($con, $newsid, $views)) {
	nm_js_notice("Views are now " . number_format($views) . ". The next reader sees the next number.", "news.php");
}

nm_js_notice("Could not save the view number.", "news.php", "error");
