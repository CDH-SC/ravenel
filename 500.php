<?php
/**
 * @file   500.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Renders the 500 error page.
 */

require_once "includes/configuration.php";

$application->setTitle("Internal Server Error");

require "layout/header.php";
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>500</h1>

      <p class="lead">This is a huge problem.</p>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <h3>What Does this Error Mean?</h3>

      <p>Your browser told our server to do something and our server imploded. <small class="text-muted">Not literally.</small></p>

      <h3>How Was this Error Caused?</h3>

      <p>99% of the time, the web developer did a poor job testing whatever link you're visiting right now. <small class="text-muted">Please tell us.</small></p>

      <h3>What Do I Do Now?</h3>

      <ul class="list-unstyled">
        <li>Tell us</li>
        <li>Try the page again</li>
        <li>Tell us</li>
      </ul>
    </div>
  </div>
</div>
<?php require "layout/footer.php"; ?>
