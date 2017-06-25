<?php
/**
 * @file   portraits.php
 * @author Collin Haines - Center for Digital Humanities
 */

require_once "includes/configuration.php";

$application->setTitle("Portraits");

require "layout/header.php";

$directory = scandir("/var/www/html/ravenel/img/gallery-small");
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>Portraits</h1>

      <p class="lead">Henry William Ravenel's personal photo album</p>
    </div>
  </div>

  <div class="row" id="gallery">
    <?php foreach ($directory as $image): ?>
      <?php
      if ($image === "." || $image === "..") { continue; }

      $detail = json_decode(file_get_contents("http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo/rav/" . substr($image, 0, -4) . "/id/json"), true);
      ?>
      <img src="<?php print ROOT_FOLDER; ?>img/gallery-small/<?php print $image; ?>" class="img-responsive" alt="<?php print $detail["title"]; ?>" data-image="<?php print ROOT_FOLDER; ?>img/gallery/<?php print $image; ?>" data-description="<?php print $detail["title"]; ?>" style="display: none;">
    <?php endforeach; ?>
  </div>
</div>
<?php require "layout/footer.php"; ?>
