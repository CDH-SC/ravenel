<?php
/**
 * @file   404.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Renders the 404 error page.
 */

require_once "includes/configuration.php";

$application->setTitle("Page Not Found");

require "layout/header.php";
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>404</h1>

      <p class="lead">That's an error.</p>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <h3>What Does this Error Mean?</h3>

      <p>Your browser talked to our server, however our server has no idea what you're wanting from it so it gave you this page.</p>

      <small class="text-muted">Like when you tell your dog to get off the couch and they climb in your lap - while you're on the couch.</small>

      <h3>How Was this Error Caused?</h3>

      <p>Simple, one of these options are your only options:</p>

      <ul class="list-unstyled">
        <li>1. The URL above no longer exists or never did exist.</li>
        <li>2. The web developer has changed URLs and did not update the link you're trying to visit now. <small class="text-muted">That's embarrassing.</small></li>
        <li>3. You're visiting this page for fun.</li>
      </ul>

      <h3>What Do I Do Now?</h3>

      <ul class="list-unstyled">
        <li>Refresh</li>
        <li>Refresh Again</li>
        <li>Go back in your browser</li>
        <li>Start from the <a href="<?php print ROOT_FOLDER; ?>">home page</a>.</li>
      </ul>
    </div>
  </div>
</div>
<?php require "layout/footer.php"; ?>
