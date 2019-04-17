<?php
/**
 * @file viewer.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * The metaphorical end-game of the Plants and Planters - Henry Ravenel site. This
 * page allows our users to view two main categories: Transcripts or Specimens. All
 * search results, map details, and random clicks will end here at one point.
 *
 * This page uses URL rewriting through the .htaccess file.
 *
 * NOTE: The terms 'Transcript' and 'Manuscript' are used interchangeably from here
 * on in unless referring to the human-typed version of text within an image.
 *
 * @param string type
 *	 Acceptable as either transcript or specimen.
 * @param string institute
 *	 Must be Carolina when the type is equal to specimen. Otherwise, is fair game
 *	 as long as it matches correctly with the manuscript pointer.
 * @param int number
 *	 Representing a pointer (transcript) or catalog number (specimen).
 * @param string search
 *	 Mainly used when arrving from search results, this'll tell the application any
 *	 word(s) to highlight.
 *
 */

	require_once("includes/configuration.php");

	// AJAX method for viewing a specimen on the manuscript viewer.
	// AJAX method for viewing a manuscript on the specimen viewer.
	if(isset($_GET["catalogNumber"]) || isset($_GET["pointer"], $_GET["institute"]))
	{
		$item = isset($_GET["catalogNumber"]) ? new Specimen($_GET["catalogNumber"]) : new Manuscript($_GET["pointer"], $_GET["institute"]);
		$type = isset($_GET["catalogNumber"]) ? "specimen" : "manuscript";

		// Panel viewer.
		$panel = '<div class="col-md-6 panel-viewer" id="'.$type.'SideViewer" style="display: none;">';

		$panel .= '<div class="panel-thumbnail"><div class="thumbnail-overlay" style="max-width: 100%;"></div><img src="'.$item->getThumb().'" class="img-responsive"></div>';

		$panel .= '<div class="col-xs-12 panel-tools"><ul class="list-inline center-block">';
		$panel .= '<li><a href="#zoom-in" class="btn btn-default zoom-plus" data-toggle="tooltip" data-placement="top" title="Zoom In"><i class="fa fa-plus"></i></a></li>';
		$panel .= '<li><a href="#zoom-out" class="btn btn-default zoom-minus" data-toggle="tooltip" data-placement="top" title="Zoom Out"><i class="fa fa-minus"></i></a></li>';
		$panel .= '<li><a href="#refresh" class="btn btn-default refresh disabled" data-toggle="tooltip" data-placement="top" title="Refresh"><i class="fa fa-refresh"></i></a></li>';
		$panel .= '<li><a href="'.$item->getImage().'" class="btn btn-default full-screen" data-effect="fancybox" data-toggle="tooltip" data-placement="top" title="Full Screen"><i class="fa fa-arrows-alt"></i></a></li>';
		$panel .= "</ul></div>";

		$panel .= '<img src="'.$item->getImage().'" class="img-responsive max-width-transition"></div>';

		// Metadata.
		$data = '<div class="col-md-6 col-xs-12" id="'.$type.'Data" style="display: none;">'.$item->renderViewerMetadata().'</div>';

		exit(json_encode(array("image" => $panel, "metadata" => $data)));
	}

	if(!isset($_GET["type"], $_GET["institute"], $_GET["number"]))
	{
		header("Location: ./index.php");
	}

	$type = $_GET["type"];
	$institute = $_GET["institute"];
	$number = $_GET["number"];

	if($institute != "Clemson" && $institute != "Carolina")
	{
		$institute = "Carolina"; // give a default
	}

	//if the numbers do not align with their correct institutions, correct this
	if($number < 2000 && $institute == "Carolina")
	{
		$institute = "Clemson";
	}
	else if($number > 2000 && $institute = "Clemson")
	{
		$institute = "Carolina";
	}

	// Define if there is a search.
	$search = isset($_GET["search"]) ? str_replace("-"," ",$_GET["search"]) : "";

	// Alert the page if it is a photograph.
	$isPhoto = 9088 < $number && $number < 9154 && $type == "transcript";

	try
	{
		if($type == "transcript")
		{
			$manuscript = new Manuscript($number,$institute);
			$manuscript->setSearch($search);

			// Set stuff within the page.
			$image = $manuscript->getImage();
			$thumb = $manuscript->getThumb();
			$title = $manuscript->getData("title");

			$application->setTitle($title." - Manuscript Viewer");
		}
		else if($type == "specimen")
		{
			$specimen = new Specimen($number);
			$specimen->setSearch($search);

			// Set stuff within the page.
			$image = $specimen->getImage();
			$thumb = $specimen->getThumb();
			$title = $specimen->getName();

			$application->setTitle($title." - Specimen Viewer");
		}
		else
		{
			throw new Exception("Improper type: ".$type);
		}

		require("layout/header.php");
	}
	catch(Exception $e)
	{
		$application->setTitle("Viewer");

		require("layout/header.php");
?>
	<div class="container">
		<div class="row page-header" style="padding-top: 5vh; padding-bottom: 20vh;">
			<div class="col-xs-12">
				<h1>Error Rendering Viewer Page</h1>
				<p class="lead">
<?php
		print $e->getMessage();
?>
				</p>
			</div>
		</div>
	</div>
<?php
	require("layout/footer.php");
	exit;
	}
?>
<div class="container">
	<div class="row page-header">
		<div class="col-xs-12">
			<h1><?=$title?></h1>
<?php
	if($search != "")
	{
?>
				<p class="text-muted">
					<a href="<?php print ROOT_FOLDER; ?>search.php?search=<?=urlencode($search)?>" class="text-muted">Return to Search Results</a>
				</p>
<?php
	}
?>
		</div>
	</div>
	<div class="row">
		<div class="<?=($isPhoto ? "col-xs-12" : "col-md-6")?> panel-viewer height-transition" id="mainViewer" style="height: <?=($isPhoto ? "1000px;" : "500px;")?>">
			<div class="image-loading-icon">
				<i class="fa fa-refresh fa-3x fa-spin"></i>
			</div>
			<div class="panel-thumbnail">
				<div class="thumbnail-overlay max-width-height-transition" style="max-width: 100%;"></div>
				<img src="<?=$thumb?>" class="img-responsive">
			</div>
			<div class="col-xs-12 panel-tools">
				<ul class="list-inline center-block">
					<li>
						<a href="#zoom-in" class="btn btn-default zoom-plus" data-toggle="tooltip" data-placement="top" title="Zoom In">
							<i class="fa fa-plus"></i>
						</a>
					</li>
					<li>
						<a href="#zoom-out" class="btn btn-default zoom-minus" data-toggle="tooltip" data-placement="top" title="Zoom Out">
							<i class="fa fa-minus"></i>
						</a>
					</li>
					<li>
						<a href="#refresh" class="btn btn-default refresh disabled" data-toggle="tooltip" data-placement="top" title="Refresh">
							<i class="fa fa-refresh"></i>
						</a>
					</li>
					<li>
						<a href="<?=$image?>" class="btn btn-default full-screen" data-effect="fancybox" data-toggle="tooltip" data-placement="top" title="Full Screen">
							<i class="fa fa-arrows-alt"></i>
						</a>
					</li>
				</ul>
			</div>
			<img src="<?=$image?>" class="img-responsive max-width-height-transition" alt="<?=$title?>" id="mainImage">
		</div>
<?php
	if(!$isPhoto)
	{
?>
			<div class="col-md-6 panel-viewer panel-reading">
				<div class="panel-content">
<?php
		if($type == "transcript")
		{
			$renderData = false;
			$transcript = $manuscript->getData("transc");

			if($transcript == "")
			{
				$renderData = true;
				print $manuscript->renderViewerMetadata();
			}
			else
			{
				print '<pre>'.$application->highlightSearchTerm($transcript,$search).'</pre>';
			}
		}
		else if($type == "specimen")
		{
			print $specimen->renderViewerMetadata();
		}
?>
				</div>
			</div>
<?php
	}
?>
	</div>
<?php
	if($type == "transcript")
	{
?>
	<div class="row" style="margin-top: 1.5%;">
		<div class="col-md-6 text-center pages width-transition">
<?php
		print $manuscript->retrieveSiblings();
?>
		</div>

		<div class="col-md-6 text-center" id="toggleRightPanel" style="display: none;"></div>
	</div>
<?php
	}
?>
	<hr>
<?php
	if(!$isPhoto)
	{
?>
	<div class="row">
<?php
		if($type == "transcript" && !$renderData)
		{
?>
			<div class="col-md-6 col-xs-12 left-transition" id="viewerMetadata" style="left: 0;">
<?php
			print $manuscript->renderViewerMetadata();
?>
			</div>
<?php
		}
		else if($type == "specimen")
		{
			print $specimen->renderJournalMentions();
			print $specimen->renderSimilarSpecimens();
		}
?>
	</div>
<?php
	}
?>
</div>
<?php require "layout/footer.php"; ?>
