<?php
	/**
	* @file   browse.php
	* @author Collin Haines - Center for Digital Humanities
	*
	* Renders the browse page.
	*/

	require_once("includes/configuration.php");

	$application->setTitle("Browse");
	$json = $application->getJSON();

	require("layout/header.php");
?>
<div class="container">
	<div class="row page-header">
		<div class="col-xs-12">
			<h1>Browse</h1>

			<p class="lead">All Content. One Location.</p>
		</div>
	</div>

	<div class="row">
<?php
	$columnHeadersToDataKeys = array("People & Organizations" => "people", "Plant Specimens" => "scient", "Locations" => "geogra", "Dates" => "date");

	foreach ($columnHeadersToDataKeys as $title => $key)
	{
?>
		<div class="col-md-3 browse-column">
			<div class="panel panel-plant">
				<div class="panel-heading">
					<h3 class="text-center"><?=$title?></h3>
<?php
		//plant specimens and people/orgs have images available
		if($key == "scient" || $key == "people")
		{
?>
					<p class="text-center" style="color: #ECECEC; font-size: 12px;">
						* Image Available
					</p>
<?php
		}
?>
				</div>
				<div class="panel-body">
					<div class="list-group">
<?php
		foreach($application->convertBrowseArray($json[$key], $key) as $letter => $value)
		{
?>
						<button type="button" class="list-group-item"><?=$letter?></button>
						<div class="list-group" style="display: none;">
<?php
			foreach($value as $data)
			{
				if(substr($data, -1) === "*")
				{
?>
							<a href="<?=ROOT_FOLDER?>search.php?search=<?=urlencode(substr($data, 0, -1))?>&amp;browse=<?=$key?>" class="list-group-item"><?=$data?></a>
<?php
				}
				else if($key == "date")
				{
?>
							<a href="<?=ROOT_FOLDER?>search.php?search=<?=urlencode($data)?>*&amp;browse=<?=$key?>" class="list-group-item"><?=$data?></a>
<?php
				}
				else
				{
?>
							<a href="<?=ROOT_FOLDER?>search.php?search=<?=urlencode($data)?>&amp;browse=<?=$key?>" class="list-group-item"><?=$data?></a>
<?php 
				}
			}
?>
						</div>
<?php
		}
?>
					</div>
				</div>
			</div>
		</div>
<?php 
	}
?>
	</div>

	<div class="row">
		<div class="col-xs-12">
			<p>If you're unable to find an item in this page, try <a href="<?=ROOT_FOLDER?>search.php">searching</a> for it.</p>
		</div>
	</div>
</div>
<?php require "layout/footer.php"; ?>
