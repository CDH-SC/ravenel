<?php
	/**
	 * @file   search.php
	 * @author Collin Haines - Center for Digital Humanities
	 *
	 * Renders the search page.
	 *
	 * Two sides of this page exists:
	 * 1. The search query entry.
	 * 2. The search results.
	 */

	require_once "includes/configuration.php";

	$application->setTitle("Search");

	require("layout/header.php");
	
	if(isset($_GET["search"]) || isset($_GET["options"],$_GET["input"]))
	{
?>
	<div class="container">
		<div class="row page-header">
			<div class="col-xs-12">
				<h1>Search Results</h1>
				<p class="lead">Don't get lost.</p>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<ul class="nav search-tabs">
					<li class="active">
						<a href="#journals" data-toggle="tab" aria-expanded="true">Journals <span class="text-muted">(<span>0</span>)</span></a>
					</li>
					<li class="disabled">
						<a href="#letters" data-toggle="tab" aria-expanded="false">Letters <span class="text-muted">(<span>0</span>)</span></a>
					</li>
					<li class="disabled">
						<a href="#specimens" data-toggle="tab" aria-expanded="false">Plant Specimens <span class="text-muted">(<span>0</span>)</span></a>
					</li>
					<li class="disabled">
						<a href="#photographs" data-toggle="tab" aria-expanded="false">Portraits <span class="text-muted">(<span>0</span>)</span></a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane fade active in" id="journals">
<?php
		$array = array("Thumbnail", "Date", "People", "Location", "Transcript", "Scientific Plant Names", "Common Plant Names", "Title", "Coordinates", "Description");
?>
						<ul class="list-inline" data-toggle="columns">
							<li>Toggle Columns:</li>

<?php
		for ($i = 0; $i < count($array); $i++)
		{
?>
							<li><a href="#remove-column-<?=$i?>" class="<?=($i < 5 ? "is-visible" : "is-not-visible")?>" data-column="<?=$i?>"><?=$array[$i]?></a></li>
<?php
		}
?>
						</ul>

						<table class="table">
							<thead>
								<tr>
<?php
		foreach($array as $header)
		{
?>
									<th><?=$header?></th>
<?php
		}
?>
								</tr>
							</thead>
						</table>
					</div>
					<div class="tab-pane fade" id="letters" style="position: absolute; display: block; pointer-events: none;">
<?php
		$array = array("Thumbnail", "Date", "People", "Title", "Location", "Description", "Scientific Plant Names", "Common Plant Names", "Coordinates", "Transcript");
?>
						<ul class="list-inline" data-toggle="columns">
							<li>Toggle Columns:</li>

<?php
		for($i = 0; $i < count($array); $i++)
		{
?>
							<li><a href="#remove-column-<?=$i?>" class="<?php print $i < 5 ? "is-visible" : "is-not-visible"; ?>" data-column="<?=$i?>"><?=$array[$i]?></a></li>
<?php
		}
?>
						</ul>

						<table class="table">
							<thead>
								<tr>
<?php
		foreach($array as $header)
		{
?>
									<th><?=$header?></th>
<?php
		}
?>
								</tr>
							</thead>
						</table>
					</div>

					<div class="tab-pane fade" id="specimens" style="position: absolute; display: block; pointer-events: none;">
<?php
		$array = array("Thumbnail", "Scientific Plant Names", "Date", "Identified By", "Location", "Coordinates", "Habitat", "Collected By", "Cultivation Status");
?>
						<ul class="list-inline" data-toggle="columns">
							<li>Toggle Columns:</li>

<?php
		for($i = 0; $i < count($array); $i++)
		{
			?>
							<li><a href="#remove-column-<?=$i?>" class="<?php print $i < 5 ? "is-visible" : "is-not-visible"; ?>" data-column="<?=$i?>"><?=$array[$i]?></a></li>
<?php
		}
?>
						</ul>

						<table class="table">
							<thead>
								<tr>
<?php
		foreach($array as $header)
		{
?>
									<th><?=$header?></th>
<?php
		}
?>
								</tr>
							</thead>
						</table>
					</div>
					<div class="clearfix tab-pane fade" id="photographs"></div>
				</div>
			</div>
		</div>
	</div>
<?php
	}
	else
	{
		// Value (server help) => Option (user help)
		$optionsAll = array(
			"CISOSEARCHALL"          => "All Fields",
			"date-eventDate"         => "Date",
			"geogra-locality"        => "Location",
			"scientificName-sciname" => "Scientific Plants"
		);

		$optionsManuscripts = array(
			"common" => "Common Plants",
			"date"   => "Date",
			"descri" => "Description",
			"people" => "People",
			"geogra" => "Places",
			"scient" => "Scientific Plants",
			"subjec" => "Subject",
			"title"  => "Title",
			"transc" => "Transcript"
		);

		$optionsSpecimens = array(
			"recordedBy"     => "Collected By",
			"eventDate"      => "Date",
			"family"         => "Family",
			"habitat"        => "Habitat",
			"identifiedBy"   => "Identified By",
			"coordinates"    => "Latitude/Longitude",
			"location"       => "Location",
			"scientificName" => "Scientific Name"
		);
?>

	<div class="container">
		<div class="row page-header">
			<div class="col-xs-12">
				<h1>Search</h1>
				<p class="lead">Three Databases. Limitless Searches.</p>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3" id="basicLive"></div>

			<div class="col-sm-6">
				<form class="form-horizontal" action="<?php print ROOT_FOLDER; ?>search.php" autocomplete="off" enctype="application/x-www-form-urlencoded">
					<legend>Basic Search</legend>
					<fieldset>
						<div class="form-group">
							<div class="col-xs-12" data-step data-step-title="Basic Search" data-step-content="Type in a word. Press Search. Get excited!" data-step-placement="top" style="padding: 0;">
								<label for="basicSearch" class="hide">Search</label>

								<div class="col-sm-10 col-sm-spacing">
									<input type="text" class="form-control" id="basicSearch" name="search" autofocus required>
								</div>

								<div class="col-sm-2">
									<button type="submit" class="btn btn-plant">Search</button>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-sm-3" id="advancedLive"></div>

			<div class="col-sm-6" data-step data-step-title="Advanced Search" data-step-content="Add in some words, take out some other words, maybe include that one word." data-step-placement="top">
				<form class="form-horizontal" id="advancedForm" action="<?php print ROOT_FOLDER; ?>search.php" autocomplete="off" enctype="application/x-www-form-urlencoded">
					<fieldset>
						<legend>Advanced Search <span id="tourStart" class="advanced-help">Help</span></legend>

						<div class="form-group">
							<div class="col-sm-4 col-sm-spacing" data-step data-step-title="Customized Search Fields" data-step-content="Customize the ability to search within a specific field." data-step-placement="right">
								<select class="form-control" name="options[]">
									<optgroup label="All">
<?php
		foreach($optionsAll as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
									<optgroup label="Manuscripts">
<?php
		foreach($optionsManuscripts as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
									<optgroup label="Specimens">
<?php
		foreach($optionsSpecimens as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
								</select>
							</div>

							<div class="col-sm-4 col-sm-spacing">
								<input type="text" class="form-control" name="input[]" required>
							</div>

							<div class="col-sm-4 col-sm-border">
								<select class="form-control" name="operators[]">
									<option value="and">And</option>
									<option value="none">Not</option>
									<option value="or">Or</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-4 col-sm-spacing">
								<select class="form-control" name="options[]">
									<optgroup label="All">
<?php
		foreach($optionsAll as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
									<optgroup label="Manuscripts">
<?php
		foreach($optionsManuscripts as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
									<optgroup label="Specimens">
<?php
		foreach($optionsSpecimens as $value => $option)
		{
?>
										<option value="<?=$value?>"><?=$option?></option>
<?php
		}
?>
									</optgroup>
								</select>
							</div>
							<div class="col-sm-4 col-sm-spacing">
								<input type="text" class="form-control" name="input[]">
							</div>
						</div>

						<div class="form-group clearfix">
							<div class="col-xs-12">
								<button type="button" class="btn btn-plant pull-left" id="addAdvancedRow">Add Row</button>
								<button type="submit" class="btn btn-plant pull-right">Search</button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
<?php
	}

	require("layout/footer.php");
?>
