<?php
include "config.php";
require_once __DIR__ . "/admin_helpers.php";
require_once __DIR__ . "/site_settings_lib.php";
require_once __DIR__ . "/../nm/home.php";

if (!isset($_SESSION["aemail"])) {
	$_SESSION["msg"] = "You must log in first";
	header("location: ../manage.php");
	exit;
}

if (isset($_GET["logout"])) {
	session_destroy();
	unset($_SESSION["aemail"]);
	header("location: ../manage.php");
	exit;
}

$usersession = $_SESSION["aemail"];
$res = mysqli_query($con, "SELECT * FROM admin WHERE aemail='" . mysqli_real_escape_string($con, $usersession) . "'");
$userRow = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;
nm_require_admin($con);
nm_ensure_site_settings($con);

$msg = "";
$err = "";

function nm_menu_rows($con)
{
	$sql = "SELECT id, hindi_name, maincat, parent, cat_url, short, menu
		FROM categories
		WHERE cat_url IS NOT NULL AND cat_url != ''
		  AND hindi_name IS NOT NULL AND hindi_name != ''
		ORDER BY id ASC";
	$q = mysqli_query($con, $sql);
	$rows = array();
	if ($q) {
		while ($row = mysqli_fetch_assoc($q)) {
			$rows[] = $row;
		}
	}
	return $rows;
}

if (isset($_POST["save_menu"])) {
	$rows = nm_menu_rows($con);
	$show = isset($_POST["show"]) && is_array($_POST["show"]) ? $_POST["show"] : array();
	$ord = isset($_POST["ord"]) && is_array($_POST["ord"]) ? $_POST["ord"] : array();
	$stmt = mysqli_prepare($con, "UPDATE categories SET menu=?, `short`=? WHERE id=? LIMIT 1");
	if (!$stmt) {
		$err = "Could not save the menu.";
	} else {
		$menuVal = "No";
		$shortVal = "0";
		$idVal = 0;
		mysqli_stmt_bind_param($stmt, "ssi", $menuVal, $shortVal, $idVal);
		$ok = true;
		foreach ($rows as $row) {
			$idVal = (int) $row["id"];
			$menuVal = isset($show[$idVal]) ? "Yes" : "No";
			$num = isset($ord[$idVal]) ? (int) $ord[$idVal] : 0;
			if ($num < 0) {
				$num = 0;
			}
			if ($num > 999) {
				$num = 999;
			}
			$shortVal = (string) $num;
			if (!mysqli_stmt_execute($stmt)) {
				$ok = false;
			}
		}
		mysqli_stmt_close($stmt);
		if ($ok && nm_setting_set($con, "top_menu_ready", "1")) {
			$msg = "Top menu saved. Refresh the public site to see the blue bar.";
		} else {
			$err = "Could not save the menu.";
		}
	}
}

$ready = nm_setting_get($con, "top_menu_ready", "") === "1";
$rows = nm_menu_rows($con);
$names = array();
foreach ($rows as $row) {
	$label = trim((string) $row["hindi_name"]);
	if ($label === "") {
		$label = trim((string) $row["maincat"]);
	}
	$names[(string) $row["id"]] = $label;
}

$checked = array();
$preset = array();
if ($ready) {
	foreach ($rows as $row) {
		if (isset($row["menu"]) && $row["menu"] === "Yes") {
			$checked[(int) $row["id"]] = true;
		}
	}
} else {
	$step = 10;
	foreach (nm_main_categories() as $cat) {
		$id = (int) $cat["id"];
		$checked[$id] = true;
		$preset[$id] = $step;
		$step += 10;
	}
}

foreach ($rows as $i => $row) {
	$id = (int) $row["id"];
	if (isset($preset[$id])) {
		$rows[$i]["_ord"] = $preset[$id];
	} else {
		$rows[$i]["_ord"] = (int) $row["short"];
	}
	$rows[$i]["_on"] = isset($checked[$id]);
}

usort($rows, function ($a, $b) {
	if ($a["_on"] !== $b["_on"]) {
		return $a["_on"] ? -1 : 1;
	}
	if ($a["_on"] && (int) $a["_ord"] !== (int) $b["_ord"]) {
		return (int) $a["_ord"] - (int) $b["_ord"];
	}
	return strcasecmp((string) $a["hindi_name"], (string) $b["hindi_name"]);
});

$nmHeadBrand = function_exists("nm_brand_mark") ? nm_brand_mark($con) : array("favicon" => "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Top menu — Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <link rel="stylesheet" href="css/admin-modern.css?v=16">
  <script src="../include/js/jquery.min.js"></script>
  <?php if (!empty($nmHeadBrand["favicon"])) { ?>
  <link rel="icon" href="<?php echo nm_h($nmHeadBrand["favicon"]); ?>">
  <?php } ?>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Top menu</li>
    </ol>
    <div class="container-fluid page-content" style="max-width:860px;">
      <h2 style="margin-top:0;">Top menu</h2>
      <p class="text-muted">Tick the categories you want in the blue bar under the logo. A smaller number shows first. The home icon stays first. Unticked names stay off that bar.</p>
      <?php if ($msg) { ?><div class="alert alert-success"><?php echo nm_h($msg); ?></div><?php } ?>
      <?php if ($err) { ?><div class="alert alert-danger"><?php echo nm_h($err); ?></div><?php } ?>
      <form method="post" class="card" style="padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
        <div class="form-group">
          <label for="menu-find">Find a category</label>
          <input class="form-control" id="menu-find" type="search" placeholder="Type a name" autocomplete="off">
        </div>
        <div class="table-responsive">
          <table class="table table-bordered" style="margin-bottom:12px;">
            <thead>
              <tr>
                <th style="width:70px;">Show</th>
                <th style="width:90px;">Order</th>
                <th>Category</th>
                <th>Under</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row):
                $id = (int) $row["id"];
                $parentId = isset($row["parent"]) ? (string) $row["parent"] : "0";
                $under = ($parentId !== "" && $parentId !== "0" && isset($names[$parentId])) ? $names[$parentId] : "—";
              ?>
              <tr data-name="<?php echo nm_h($row["hindi_name"] . " " . $row["cat_url"]); ?>">
                <td style="text-align:center;">
                  <input type="checkbox" name="show[<?php echo $id; ?>]" value="1"<?php echo $row["_on"] ? " checked" : ""; ?>>
                </td>
                <td>
                  <input class="form-control" type="number" min="0" max="999" name="ord[<?php echo $id; ?>]" value="<?php echo (int) $row["_ord"]; ?>">
                </td>
                <td>
                  <?php echo nm_h($row["hindi_name"]); ?>
                  <div class="text-muted" style="font-size:12px;">/category/<?php echo nm_h($row["cat_url"]); ?></div>
                </td>
                <td><?php echo nm_h($under); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" name="save_menu" value="1" class="btn btn-danger">Save top menu</button>
      </form>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
<script>
document.getElementById("menu-find").addEventListener("input", function () {
  var q = this.value.toLowerCase();
  var rows = document.querySelectorAll("tr[data-name]");
  for (var i = 0; i < rows.length; i++) {
    var name = (rows[i].getAttribute("data-name") || "").toLowerCase();
    rows[i].style.display = name.indexOf(q) === -1 ? "none" : "";
  }
});
</script>
</body>
</html>
