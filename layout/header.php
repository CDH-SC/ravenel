<?php
/**
 * @file   header.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * HTML header and beginning structure printer.
 *
 * This file must:
 *
 *   Be imported after `/var/www/html/ravenel/includes/application.php`.
 *   Be imported before any `<div class="container"></div>`.
 *
 */

if (isset($_POST["data"], $_POST["data"]["name"], $_POST["data"]["email"], $_POST["data"]["category"], $_POST["data"]["message"], $_POST["data"]["response"], $_POST["data"]["url"], $_POST["data"]["platform"])) {
  print $application->sendMail($_POST["data"]["name"], $_POST["data"]["email"], $_POST["data"]["category"], $_POST["data"]["message"], $_POST["data"]["response"], $_POST["data"]["url"], $_POST["data"]["platform"]);
  exit;
} // if (isset($_POST["data"]))

?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <meta name="author" content="Center for Digital Humanities - University of South Carolina">
  <meta name="keywords" content="henry, william, ravenel, plants, planter, center, digital, humanities, south, carolina">
  <meta name="description" content="Henry William Ravenel, a botanist, planter, and author documented over 6,200 botanical specimens and wrote over 3,000 pages of field journals and correspondence.">

  <?php foreach (array(57, 60, 72, 76, 114, 120, 144, 152, 180) as $size): ?>
    <link rel="apple-touch-icon" sizes="<?php print $size; ?>x<?php print $size; ?>" href="<?php print ROOT_FOLDER; ?>apple-touch-icon-<?php print $size; ?>x<?php print $size; ?>.png">
  <?php endforeach; ?>

  <link rel="icon" type="image/png" href="<?php print ROOT_FOLDER; ?>android-chrome-192x192.png" sizes="192x192">
  <link rel="icon" type="image/png" href="<?php print ROOT_FOLDER; ?>favicon-96x96.png" sizes="96x96">
  <link rel="icon" type="image/png" href="<?php print ROOT_FOLDER; ?>favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="<?php print ROOT_FOLDER; ?>favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="<?php print ROOT_FOLDER; ?>manifest.json">
  <link rel="mask-icon" href="<?php print ROOT_FOLDER; ?>safari-pinned-tab.svg" color="#5bbad5">
  <meta name="apple-mobile-web-app-title" content="Plants and Planter: Henry William Ravenel">
  <meta name="application-name" content="Plants and Planter: Henry William Ravenel">
  <meta name="msapplication-TileColor" content="#00a300">
  <meta name="msapplication-TileImage" content="<?php print ROOT_FOLDER; ?>mstile-144x144.png">
  <meta name="theme-color" content="#ffffff">

  <title><?php print trim($application->getTitle()) === "" ? "" : trim($application->getTitle()) . " - "; ?>Henry William Ravenel - Plants and Planter</title>

  <?php
  /*
   * CSS Libraries Used:
   *
   *   Bootstrap - getbootstrap.com
   *     Used as a framework for the entire website.
   *
   *   Bootstrap-Tour - bootstraptour.com
   *     Used as a tutorial on how to use the search page.
   *
   *   DataTables - datatables.net
   *     Used as an organizer for the search results.
   *
   *   Fancybox - fancyapps.com/fancybox/
   *     Used as a full-screen view of images on the viewer page.
   *
   *   Font-Awesome - fontawesome.io
   *     Used as a replacement of Bootstrap's default Glyphicons.
   *
   *   Slick - kenwheeler.github.io/slick/
   *     Used as a carousel for multiple related items on the viewer page.
   *
   *   Unite Gallery - unitegallery.net/
   *     Used as a gallery on the gallery page.
   *
   *
   * All other files are to be assumed in-house created.  Development versions can be
   * found in the following folder:
   *
   *   /ravenel/css/src/
   *
   */
  ?>
  <?php foreach (array("bootstrap.min.css", "font-awesome.min.css", "datatables.min.css", "fancybox.min.css", "slick.min.css", "slick-theme.min.css", "bootstrap-tour.min.css", "unite-gallery.min.css", "ravenel.min.css") as $class): ?>
    <link rel="stylesheet" href="<?php print ROOT_FOLDER; ?>css/<?php print $class; ?>">
  <?php endforeach; ?>

  <?php
  /*
   * The following stylesheet is to be used only for the Correspondences Map on the
   * map page.
   */
  ?>
  <?php if (strpos($application->getTitle(), "Correspondence") !== false && strpos($application->getTitle(), "Map") !== false): ?>
    <link rel="stylesheet" href="//js.arcgis.com/3.15/esri/css/esri.css">
  <?php endif; ?>

  <?php
  /*
   * Google Analytics Script.
   *
   * Google Analytics Login and Password are within 'the book'.
   */
  ?>
  <script>
    (function(i, s, o, g, r, a, m) {
      i['GoogleAnalyticsObject'] = r;

      i[r] = i[r] || function () {
        (i[r].q = i[r].q || []).push(arguments)
      },
      i[r].l = 1 * new Date();

      a = s.createElement(o),
      m = s.getElementsByTagName(o)[0];

      a.async = 1;

      a.src = g;
      m.parentNode.insertBefore(a,m);
    })(window, document, 'script', '//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-66894560-1', 'auto');
    ga('send', 'pageview');
  </script>
</head>
<body>
  <header>
    <div class="container">
      <div class="row">
        <div class="col-md-6 col-xs-12">
          <a href="<?php print ROOT_FOLDER; ?>" class="logo">
            <img src="<?php print ROOT_FOLDER; ?>img/logo.png" class="img-responsive center-block" alt="Plants and Planter">
          </a>
        </div>

        <nav class="col-md-6 col-xs-12">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

          <div class="collapse navbar-collapse" id="menu">
            <ul class="nav navbar-nav navbar-right">
              <?php foreach (array("Home", "About", "Browse", "Search") as $nav): ?>
                <li><a href="<?php print ROOT_FOLDER; print $nav === "Home" ? "" : strtolower($nav); ?>"><?php print $nav; ?></a></li>
              <?php endforeach; ?>

              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Maps <span class="caret"></span></a>

                <ul class="dropdown-menu" role="menu">
                  <li><a href="<?php print ROOT_FOLDER; ?>map?type=travel">Travel Map</a></li>
                  <li class="divider"></li>
                  <li><a href="<?php print ROOT_FOLDER; ?>map?type=letters">Correspondence Map</a></li>
                  <li class="divider"></li>
                  <li><a href="<?php print ROOT_FOLDER; ?>map?type=plants">Plant Specimens Map</a></li>
                </ul>
              </li>

              <li><a href="<?php print ROOT_FOLDER; ?>portraits">Portraits</a></li>

              <li><a href="<?php print ROOT_FOLDER; ?>fungi">Fungi</a></li>

              <li><a href="#" id="search"><i class="fa fa-search" style="font-size: 14px;"></i></a></li>
            </ul>
          </div>
        </nav>

        <form class="form-horizontal pull-right" action="<?php print ROOT_FOLDER; ?>search" autocomplete="off" style="display: none; clear: right;" id="submit-search">
          <legend class="hide">Menu Search Form</legend>

          <fieldset>
            <div class="form-group header-search">
              <div class="col-xs-6 pull-right">
                <input type="text" class="form-control" name="search" value="<?php print isset($_GET["search"]) ? $_GET["search"] : ""; ?>" placeholder="Search">
              </div>

              <button type="submit" class="btn btn-plant pull-right">Search</button>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
  </header>

  <div class="alert alert-danger hide" id="alertBrowser">
    <p class="text-center">Your browser is not recommended to view this website. Please upgrade to a newer version.</p>
  </div>
