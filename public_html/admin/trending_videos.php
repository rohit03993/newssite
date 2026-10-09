<?php
include "config.php";
require_once __DIR__ . "/../nm/reels.php";

if (!isset($_SESSION["aemail"])) {
    $_SESSION["msg"] = "You must log in first";
    header("location: ../manage.php");
    exit;
}

if (!function_exists("nm_require_admin")) {
    require_once __DIR__ . "/admin_helpers.php";
}
nm_require_admin($con);

$usersession = $_SESSION["aemail"];
$res = mysqli_query($con, "SELECT * FROM admin WHERE aemail='" . mysqli_real_escape_string($con, $usersession) . "'");
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

$msg = "";
$err = "";
if (!nm_fb_reels_ensure($con)) {
    $err = "The video list could not be prepared. Check the database, then open this page again.";
}

function nm_fb_reel_rows($con)
{
    $rows = array();
    $q = mysqli_query($con, "SELECT * FROM `facebook_reels` ORDER BY `sort_order` ASC, `id` DESC");
    if ($q) {
        while ($row = mysqli_fetch_assoc($q)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

if ($err === "" && isset($_POST["add_reel"])) {
    $url = nm_fb_clean_url(isset($_POST["reel_url"]) ? $_POST["reel_url"] : "");
    $title = trim((string) (isset($_POST["title"]) ? $_POST["title"] : ""));
    if (function_exists("mb_substr")) {
        $title = mb_substr($title, 0, 120);
    } else {
        $title = substr($title, 0, 120);
    }
    if ($url === "") {
        $err = "Paste a Facebook video link, like https://www.facebook.com/reel/1457836846211670";
    } else {
        $countQ = mysqli_query($con, "SELECT COUNT(*) AS c FROM `facebook_reels`");
        $countRow = $countQ ? mysqli_fetch_assoc($countQ) : null;
        $count = $countRow ? (int) $countRow["c"] : 0;
        if ($count >= 12) {
            $err = "12 videos is the limit. Delete one, then add another.";
        } else {
            $safeUrl = mysqli_real_escape_string($con, $url);
            $dup = mysqli_query($con, "SELECT `id` FROM `facebook_reels` WHERE `reel_url`='$safeUrl' LIMIT 1");
            if ($dup && mysqli_fetch_assoc($dup)) {
                $err = "That video is already in the list.";
            } else {
                $minQ = mysqli_query($con, "SELECT MIN(`sort_order`) AS m FROM `facebook_reels`");
                $minRow = $minQ ? mysqli_fetch_assoc($minQ) : null;
                $sort = ($minRow && $minRow["m"] !== null) ? ((int) $minRow["m"] - 1) : 1;
                $safeTitle = mysqli_real_escape_string($con, $title);
                $ok = mysqli_query(
                    $con,
                    "INSERT INTO `facebook_reels` (`reel_url`, `title`, `sort_order`, `is_on`) VALUES ('$safeUrl', '$safeTitle', '$sort', 1)"
                );
                if ($ok) {
                    $msg = "Video added. It now shows on every news story.";
                } else {
                    $err = "Could not save that video.";
                }
            }
        }
    }
}

if ($err === "" && isset($_POST["reel_id"])) {
    $id = (int) $_POST["reel_id"];
    if ($id > 0 && isset($_POST["delete_reel"])) {
        mysqli_query($con, "DELETE FROM `facebook_reels` WHERE `id`='$id' LIMIT 1");
        $msg = "Video removed.";
    } elseif ($id > 0 && isset($_POST["toggle_reel"])) {
        mysqli_query($con, "UPDATE `facebook_reels` SET `is_on` = IF(`is_on`=1, 0, 1) WHERE `id`='$id' LIMIT 1");
        $msg = "Updated.";
    } elseif ($id > 0 && (isset($_POST["move_up"]) || isset($_POST["move_down"]))) {
        $rows = nm_fb_reel_rows($con);
        $index = -1;
        foreach ($rows as $i => $row) {
            if ((int) $row["id"] === $id) {
                $index = $i;
                break;
            }
        }
        $swap = isset($_POST["move_up"]) ? $index - 1 : $index + 1;
        if ($index >= 0 && isset($rows[$swap])) {
            $a = (int) $rows[$index]["sort_order"];
            $b = (int) $rows[$swap]["sort_order"];
            $idB = (int) $rows[$swap]["id"];
            if ($a === $b) {
                $b = isset($_POST["move_up"]) ? $a - 1 : $a + 1;
            }
            mysqli_query($con, "UPDATE `facebook_reels` SET `sort_order`='$b' WHERE `id`='$id' LIMIT 1");
            mysqli_query($con, "UPDATE `facebook_reels` SET `sort_order`='$a' WHERE `id`='$idB' LIMIT 1");
            $msg = "Order updated.";
        }
    }
}

$reels = ($err === "The video list could not be prepared. Check the database, then open this page again.")
    ? array()
    : nm_fb_reel_rows($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Trending videos — Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../include/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="../include/css/style.css">
  <script src="../include/js/jquery.min.js"></script>
</head>
<body>
<div class="wrapper">
  <?php include "sidebar.php"; ?>
  <div id="content">
    <?php include "header.php"; ?>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a> <i class="fa fa-angle-right"></i> Trending videos</li>
    </ol>
    <div class="container-fluid page-content nm-reels-page">
      <h2 style="margin-top:0;">Trending videos</h2>
      <p class="text-muted">Paste a public Facebook reel link. It appears in the trending row on every news story. The video stays on Facebook.</p>

      <?php if ($msg !== "") { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
      <?php } ?>
      <?php if ($err !== "") { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
      <?php } ?>

      <form method="post" class="nm-reel-add">
        <div class="form-group">
          <label for="reel_url">Facebook video link</label>
          <input type="url" class="form-control" id="reel_url" name="reel_url" required placeholder="https://www.facebook.com/reel/1457836846211670" value="<?php echo $err !== "" && isset($_POST["reel_url"]) ? htmlspecialchars((string) $_POST["reel_url"]) : ""; ?>">
        </div>
        <div class="form-group">
          <label for="title">Short title <span class="text-muted">(optional)</span></label>
          <input type="text" class="form-control" id="title" name="title" maxlength="120" placeholder="भारत के रत्न हैं">
        </div>
        <button type="submit" name="add_reel" value="1" class="btn btn-info">Add video</button>
      </form>

      <?php if (!$reels) { ?>
        <p class="text-muted" style="margin-top:18px;">No videos yet. The trending row stays hidden until you add one.</p>
      <?php } else { ?>
        <div class="nm-reel-list">
          <?php foreach ($reels as $i => $reel) {
              $on = (int) $reel["is_on"] === 1;
              $label = trim((string) $reel["title"]);
              if ($label === "") {
                  $label = $reel["reel_url"];
              }
          ?>
            <article class="nm-reel-item<?php echo $on ? "" : " is-off"; ?>">
              <div class="nm-reel-main">
                <strong><?php echo htmlspecialchars($label); ?></strong>
                <a href="<?php echo htmlspecialchars($reel["reel_url"]); ?>" target="_blank" rel="noreferrer"><?php echo htmlspecialchars($reel["reel_url"]); ?></a>
                <span class="nm-reel-state"><?php echo $on ? "Showing" : "Hidden"; ?></span>
              </div>
              <div class="nm-reel-actions">
                <form method="post">
                  <input type="hidden" name="reel_id" value="<?php echo (int) $reel["id"]; ?>">
                  <button type="submit" name="move_up" value="1" class="btn btn-light" <?php echo $i === 0 ? "disabled" : ""; ?> title="Move earlier">↑</button>
                  <button type="submit" name="move_down" value="1" class="btn btn-light" <?php echo $i === count($reels) - 1 ? "disabled" : ""; ?> title="Move later">↓</button>
                  <button type="submit" name="toggle_reel" value="1" class="btn btn-light"><?php echo $on ? "Hide" : "Show"; ?></button>
                  <button type="submit" name="delete_reel" value="1" class="btn btn-danger" data-nm-confirm="Delete this video?">Delete</button>
                </form>
              </div>
            </article>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <?php include "footer.php"; ?>
  </div>
</div>
</body>
</html>
