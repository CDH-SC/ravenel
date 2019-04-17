<?php
	/**
	 * @file portraits.php
	 * @author Collin Haines - Center for Digital Humanities
	 */

	require_once("includes/configuration.php");

	$application->setTitle("Portraits");

	require("layout/header.php");

	//TODO::AddDefine for small gallery location
	//$directory = scandir("/var/www/ravenel.com/img/gallery-small");
?>
<div class="container">
	<div class="row page-header">
		<div class="col-xs-12">
			<h1>Portraits</h1>
			<p class="lead">Henry William Ravenel's personal photo album</p>
		</div>
	</div>
	<div class="row" id="gallery" style="text-align:center;">
		<p>Please wait while the images load.</p>
	</div>
</div>
<?php
	require("layout/footer.php");
?>
