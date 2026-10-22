<?php
if (!function_exists('nm_h')) {
	require_once __DIR__ . '/admin_helpers.php';
}
$nmBrand = function_exists('nm_brand_mark') ? nm_brand_mark(isset($con) ? $con : null) : array('logo' => '', 'favicon' => '', 'title' => '');
$nmMenuTitle = $nmBrand['title'] !== '' ? $nmBrand['title'] : 'News';
if (!defined("NM_ADMIN_ASSETS")) {
	define("NM_ADMIN_ASSETS", true);
	echo '<link rel="stylesheet" href="css/admin-modern.css?v=41">' . "\n";
	echo '<style>' . nm_admin_theme_css(isset($con) ? $con : null) . '</style>' . "\n";
	if ($nmBrand['favicon'] !== '') {
		echo '<script>var nmIcon=document.createElement("link");nmIcon.rel="icon";nmIcon.href=' . json_encode($nmBrand['favicon']) . ';document.head.appendChild(nmIcon);</script>' . "\n";
	}
	echo '<script src="js/nm-dialog.js?v=1"></script>' . "\n";
}
$nmPage = basename(isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : "");
if (!isset($nmMe) || !is_array($nmMe)) {
	$nmMe = function_exists('nm_cms_identity') ? nm_cms_identity($con, isset($userRow) ? $userRow : null) : array(
		'name' => 'Admin', 'role' => 'Admin', 'is_admin' => true, 'team_id' => 0, 'avatar' => '', 'initial' => 'N',
	);
}
$nmUserPhoto = '';
$nmIsAdmin = !empty($nmMe['is_admin']);
$nmAdminOnly = array(
	'categories.php', 'edit_category.php', 'site_menu.php', 'cleanup_news.php',
	'pages.php', 'add_pages.php', 'edit_pages.php', 'post_views.php',
	'youtube_shorts.php', 'trending_videos.php', 'branding.php', 'whatsapp_share.php', 'team.php', 'admin_users.php',
);
if (!$nmIsAdmin && in_array($nmPage, $nmAdminOnly, true)) {
	nm_require_admin($con);
}
if (!function_exists("nm_nav_active")) {
	function nm_nav_active($page, $files) {
		$files = (array) $files;
		return in_array($page, $files, true) ? " is-active" : "";
	}
}
?>
<nav id="sidebar">
  <div class="sidebar-header">
    <a class="sidebar-brand" href="dashboard.php">
      <?php if ($nmBrand['logo'] !== '') { ?>
        <img class="sidebar-logo" src="<?php echo htmlspecialchars($nmBrand['logo']); ?>" alt="<?php echo htmlspecialchars($nmMenuTitle); ?>">
      <?php } else { ?>
        <?php echo htmlspecialchars($nmMenuTitle); ?>
      <?php } ?>
    </a>
    <div class="sidebar-who">
      <?php if ($nmUserPhoto !== '') { ?>
        <img class="sidebar-avatar" src="<?php echo htmlspecialchars($nmUserPhoto); ?>" alt="">
      <?php } else { ?>
        <div class="sidebar-avatar sidebar-avatar--letter"><?php echo htmlspecialchars($nmMe['initial']); ?></div>
      <?php } ?>
      <div>
        <strong><?php echo htmlspecialchars($nmMe['name']); ?></strong>
        <span><?php echo htmlspecialchars($nmMe['role']); ?></span>
      </div>
    </div>
  </div>

  <ul class="list-unstyled components">
    <p class="nav-label">Overview</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "dashboard.php"); ?>" href="dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
    </li>

    <p class="nav-label">Content</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("news.php", "add_news.php", "edit_news.php")); ?>" href="news.php">
        <i class="fas fa-newspaper"></i> News
      </a>
    </li>
    <?php if ($nmIsAdmin) { ?>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("categories.php", "edit_category.php")); ?>" href="categories.php">
        <i class="fas fa-folder-open"></i> Categories
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "site_menu.php"); ?>" href="site_menu.php">
        <i class="fas fa-bars"></i> Top menu
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, array("pages.php", "add_pages.php", "edit_pages.php")); ?>" href="pages.php">
        <i class="fas fa-file-alt"></i> Pages
      </a>
    </li>
    <?php } ?>

    <p class="nav-label">You</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "my_profile.php"); ?>" href="my_profile.php">
        <i class="fas fa-user"></i> My profile
      </a>
    </li>

    <?php if ($nmIsAdmin) { ?>
    <p class="nav-label">Site</p>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "branding.php"); ?>" href="branding.php">
        <i class="fas fa-image"></i> Branding
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "team.php"); ?>" href="team.php">
        <i class="fas fa-users"></i> Team
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "admin_users.php"); ?>" href="admin_users.php">
        <i class="fas fa-user-shield"></i> Staff
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "post_views.php"); ?>" href="post_views.php">
        <i class="fas fa-chart-bar"></i> Story views
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "trending_videos.php"); ?>" href="trending_videos.php">
        <i class="fab fa-facebook"></i> Trending videos
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "youtube_shorts.php"); ?>" href="youtube_shorts.php">
        <i class="fab fa-youtube"></i> YouTube Shorts
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "whatsapp_share.php"); ?>" href="whatsapp_share.php">
        <i class="fab fa-whatsapp"></i> WhatsApp share
      </a>
    </li>
    <li>
      <a class="<?php echo nm_nav_active($nmPage, "cleanup_news.php"); ?>" href="cleanup_news.php">
        <i class="fas fa-broom"></i> Cleanup old news
      </a>
    </li>
    <?php } ?>
  </ul>
</nav>
