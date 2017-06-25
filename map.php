<?php
/**
 * @file   map.php
 * @author Collin Haines - Center for Digital Humanities
 * @author Aysegul Yeniaras - Center for Digital Humanities (Maps)
 *
 * Renders the Map page.
 *
 * Putting this here because otherwise who knows where it will go.   If you need to
 * log into ArcGIS to edit the plant map, the following are credentials:
 *
 * Username: sccdh.        (Yes, put the period)
 * Password: Glasgow2014
 *
 * Security Question: What city where you born in?
 * Security Answer:   Glasgow
 */

// Redirection
if (!isset($_GET["type"]) || (isset($_GET["type"]) && $_GET["type"] !== "travel" && $_GET["type"] !== "letters" && $_GET["type"] !== "plants")) {
  header("Location: /map/letters");
} // if (!isset($_GET["type"]) || (isset($_GET["type"]) && $_GET["type"] !== "travel" && $_GET["type"] !== "letters" && $_GET["type"] !== "plants"))

require_once "includes/configuration.php";

if ($_GET["type"] === "travel") {
  $header = "Texas Travel Trip";
} else if ($_GET["type"] === "letters") {
  $header = "Correspondence to and from Ravenel";
} else if ($_GET["type"] === "plants") {
  $header = "Localities of Plant Specimens";
} // if ($_GET["type"] === "travel")

$application->setTitle($header . " - Maps");

require "layout/header.php";
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1><?php print $header; ?></h1>

      <?php if ($_GET["type"] === "travel"): ?>
        <p class="lead" style="margin-bottom: 10px;">Geographic representation of Ravenel's trip to Texas to investigate the causes of 'Texas cattle fever'.</p>

        <p><a href="http://biodiversitylibrary.org/page/29491027" target="_blank">Ravenel's report to Commissioner of Agriculture</a></p>
      <?php elseif ($_GET["type"] === "letters"): ?>
        <p class="lead">Geographic representation of the correspondence between Ravenel and his colleagues.</p>
      <?php elseif ($_GET["type"] === "plants"): ?>
        <p class="lead">Geographic representation of specimens collected by Ravenel both by him and through exchange.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="row <?php print $_GET["type"]; ?>-map">
    <div class="col-xs-12">
      <?php if ($_GET["type"] === "travel"): ?>
        <iframe src="//arcg.is/1ozHa39" class="map-height" id="frameMap"></iframe>
      <?php elseif ($_GET["type"] === "letters"): ?>
        <div id="map-div" style="height: 800px;"></div>
        <div id="tools-div"></div>
      <?php elseif ($_GET["type"] === "plants"): ?>
        <iframe src="//arcg.is/20KzCIX" class="map-height" id="frameMap"></iframe>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require "layout/footer.php"; ?>
