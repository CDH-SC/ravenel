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

	//To update the Texas Travel map, make sure to update the 'Ravenel Texas Travel' "Web Map" item

	// Redirection
	if(!isset($_GET["type"]) || $_GET["type"] != "travel" && $_GET["type"] != "letters" && $_GET["type"] != "plants"))
	{
		$type = "letters"; // give a default incase it isn't valid
	}
	else
	{
		$type = $_GET["type"];
	}

	require_once("includes/configuration.php");

	if ($type == "travel")
	{
		$header = "Texas Travel Trip";
	}
	else if($type == "letters")
	{
		$header = "Correspondence to and from Ravenel";
	}
	else if($type == "plants")
	{
		$header = "Localities of Plant Specimens";
	}

	$application->setTitle($header." - Maps");

	require("layout/header.php");
?>
	<div class="container">
		<div class="row page-header">
			<div class="col-xs-12">
				<h1><?=$header?></h1>

<?php
	if($type == "travel")
	{
?>
				<p class="lead" style="margin-bottom: 10px;">Geographic representation of Ravenel's trip to Texas to investigate the causes of 'Texas cattle fever'.</p>
				<p><a href="<?php echo LINK_RAVS_REPORT_COMMISSIONER; ?>" target="_blank">Ravenel's report to Commissioner of Agriculture</a></p>
<?php
	}
	else if($type == "letters")
	{
?>
				<p class="lead">Geographic representation of the correspondence between Ravenel and his colleagues.</p>
<?php
	}
	else if($type == "plants")
	{
?>
				<p class="lead">Geographic representation of specimens collected by Ravenel both by him and through exchange.</p>
<?php
	}
?>
			</div>
		</div>
		<div class="row <?=$type?>-map">
			<div class="col-xs-12">
<?php
	if($type == "travel")
	{
?>
				<iframe src="//arcg.is/1ozHa39" class="map-height" id="frameMap"></iframe>
<?php
	}
	else if($type == "letters")
	{
?>
				<div id="map-div" style="height: 800px;"></div>
				<div id="tools-div"></div>
<?php
	}
	else if($type == "plants")
	{
		//TODO::FindIFRAMEAndFixLinks
?>
				<iframe src="//arcg.is/20KzCIX" class="map-height" id="frameMap"></iframe>
<?php
	}
?>
			</div>
		</div>
	</div>
<?php
	require ("layout/footer.php");
?>