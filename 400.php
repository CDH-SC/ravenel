<?php
/**
 * @file   400.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Renders the 400 error page.
 */

require_once "includes/configuration.php";

$application->setTitle("Bad Request");

require "layout/header.php";
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>400</h1>

      <p class="lead">This is a problem.</p>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <h3>What Does this Error Mean?</h3>

      <p>Your browser tried talking to our server, but our server detected that something is "malformed" and was unable to understand what you're trying to say.</p>

      <small class="text-muted">Like when your boss emails you what to do for the day, but every other character has disappeared. So you go home for the day.</small>

      <h3>How Was this Error Caused?</h3>

      <p>95% of the time, it means there is a problem on your system (e.g. there is something unstable on your computer running the web browser)</p>

      <h3>What Do I Do Now?</h3>

      <ul class="list-unstyled">
        <li>Try again</li>
        <li>Try another browser</li>
        <li>Try another computer</li>
      </ul>
    </div>
  </div>
</div>
<?php require "layout/footer.php"; ?>
