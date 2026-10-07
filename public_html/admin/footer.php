<div class="copyrights">
  <p>© <?php echo date("Y"); ?> <?php
    $nmFoot = "News";
    if (isset($con) && function_exists("nm_brand_mark")) {
      $nmMark = nm_brand_mark($con);
      if (!empty($nmMark["title"])) {
        $nmFoot = $nmMark["title"];
      }
    }
    echo htmlspecialchars($nmFoot);
  ?></p>
</div>
<script src="js/nm-dialog.js?v=1"></script>
<script src="js/all.js" defer></script>
