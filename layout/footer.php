<?php
	/**
	 * @file   footer.php
	 * @author Collin Haines - Center for Digital Humanities
	 *
	 * HTML closing structure.
	 *
	 * This file must be at the end of every HTML page.
	 */
?>
	<footer>
		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<h4>Credits and Acknowledgments</h4>

					<ul class="list-unstyled list-anchor-white">
						<li><a href="//www.sc.edu/about/centers/digital_humanities/" target="_blank">Center for Digital Humanities</a></li>
						<li><a href="//herbarium.biol.sc.edu/" target="_blank">A. C. Moore Herbarium - University of South Carolina</a></li>
						<li><a href="//library.sc.edu/p/Collections/Digital" target="_blank">University Libraries - University of South Carolina</a></li>
						<li><a href="//www.clemson.edu/library/" target="_blank">Clemson University Libraries</a></li>
						<li><a href="//www.converse.edu" target="_blank">Converse College</a></li>
						<li><a href="//www.unc.edu" target="_blank">University of North Carolina at Chapel Hill</a></li>
						<li><a href="//www.neh.gov" target="_blank">National Endowment for the Humanities</a></li>
					</ul>
				</div>
				<div class="col-md-5">
					<h4>Feedback</h4>
					<div class="alert alert-dismissable" style="display: none;">
						<button type="button" class="close">x</button>
						<p></p>
					</div>

					<form class="form-horizontal" id="feedback">
						<fieldset>
							<div class="form-group">
								<div class="col-sm-4 col-sm-spacing">
									<input type="text" class="form-control" name="name" placeholder="Name">
								</div>
								<div class="col-sm-4 col-sm-spacing">
									<input type="email" class="form-control" name="email" placeholder="Email">
								</div>
								<div class="col-sm-4">
									<select class="form-control" name="category">
										<option value="">Category</option>
										<option value="general">General</option>
										<option value="manuscripts">Manuscripts</option>
										<option value="specimens">Specimens</option>
									</select>
								</div>
							</div>

							<div class="form-group">
								<div class="col-xs-12">
									<textarea class="form-control" name="message" rows="4" placeholder="Message"></textarea>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-9">
									<div class="g-recaptcha" data-sitekey="6Ld2sw0TAAAAAFKcnoj0N3QdQps1OG_9grECtdVz"></div>
								</div>

								<div class="col-sm-3">
									<button type="submit" class="btn btn-lg btn-plant" style="width: 100%; margin-top: 15px;">Submit</button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="col-md-3">
					<h4>Site Links</h4>

					<div class="row">
						<div class="col-md-6" style="padding-right: 0;">
							<ul class="list-unstyled list-anchor-white">
								<li><a href="<?php print ROOT_FOLDER; ?>index.php">Home</a></li>
								<li><a href="<?php print ROOT_FOLDER; ?>browse.php">Browse</a></li>
								<li><a href="<?php print ROOT_FOLDER; ?>map.php?type=letters">Correspondence Map</a></li>
								<li><a href="<?php print ROOT_FOLDER; ?>map.php?type=travel">Travel Map</a></li>
<?php
	        // BEEP BEEP. PHP COMING THROUGH.
                /*
		$json   = $application->getJSON();
                $select = rand(0, 1);

                if ($select === 0) {
                  $pointer   = rand(3900, 9200);
                  $institute = "Carolina";

                  while (in_array($pointer, $json["usc-avoid"])) {
                    $pointer = rand(3900, 9200);
                  } // while (!in_array($pointer, $json["usc-avoid"]))
                } else if ($select === 1) {
                  $pointer   = rand(700, 1500);
                  $institute = "Clemson";

                  while (in_array($pointer, $json["cu-avoid"])) {
                    $pointer = rand(700, 1500);
                  } // while (!in_array($pointer, $json["cu-avoid"]))
                } // if ($select === 0)
                ?>
                <li><a href="<?php print ROOT_FOLDER; ?>viewer/transcript/<?php print $institute; ?>/<?php print $pointer; ?>">Random Transcript</a></li>
              </ul>
            </div>

            <div class="col-md-6" style="padding-right: 0;">
              <ul class="list-unstyled list-anchor-white">
                <li><a href="<?php print ROOT_FOLDER; ?>about">About</a></li>
                <li><a href="<?php print ROOT_FOLDER; ?>search">Search</a></li>
                <li><a href="<?php print ROOT_FOLDER; ?>gallery">Gallery</a></li>
                <li><a href="<?php print ROOT_FOLDER; ?>map/plants">Plant Specimens Map</a></li>
                <?php
                // YOU THOUGHT PHP WAS DONE? NOOOPE. CHOO CHOOOO.
                $catalogs = array();
		
		// TODO Removed database query to host static site
		$statement = $mysqli->prepare("SELECT omoccurrences.otherCatalogNumbers FROM images, omoccurrences WHERE omoccurrences.collectionCode = 'HWR' AND omoccurrences.otherCatalogNumbers IS NOT NULL AND omoccurrences.occid = images.occid AND images.thumbnailurl IS NOT NULL AND images.url IS NOT NULL");
                //$statement->execute();
                $statement->bind_result($catalog);
                $statement->store_result();

                while ($statement->fetch()) {
                  array_push($catalogs, $catalog);
                } // while ($statement->fetch())

                $statement->close();

                array_multisort($catalogs);

                $random = rand($catalogs[0], end($catalogs));

                while (!in_array($random, $catalogs)) {
                  $random = rand($catalogs[0], end($catalogs));
                } // while (in_array($random, $catalogs))
                */ ?>
                <?php /*
                <li><a href="<?php print ROOT_FOLDER; ?>viewer/specimen/Carolina/<?php print $random; ?>">Random Specimen</a></li>
                */ ?>  
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</footer>

	<?php
	/*
	* JavaScript Libraries Used:
	*
	*   Bootstrap - getbootstrap.com
	*     Used as a framework for the entire website.
	*
	*   Bootstrap Tour - bootstraptour.com
	*     Used as a tutorial on how to use the search page.
	*
	*   DataTables - datatables.net
	*     Used as an organizer for the search results.
	*
	*   Fancybox - fancyapps.com/fancybox/
	*     Used as a full-screen view of images on the viewer page.
	*
	*   jQuery - jquery.com
	*     Used as a helper for JavaScript functionalities. Required by many libraries.
	*
	*   jQuery-UI - jqueryui.com
	*     Used to give users an ability to adjust width of columns on the viewer page.
	*
	*   Platform - github.com/bestiejs/platform.js/
	*     Used as a analytic tool when a user sends in a feedback form.
	*
	*   reCAPTCHA - google.com/recaptcha/
	*     Used as a bot/spam stopper tool for the feedback form.
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
	*   /ravenel/js/src/
	*
	*/
	$javaScriptMinIncludes = array("jquery.min.js", "jquery-ui.min.js", "bootstrap.js", "datatables.min.js", "fancybox.min.js", "slick.min.js", "bootstrap-tour.min.js", "platform.min.js", "unitegallery.min.js", "ug-theme-tiles.min.js");//, "ravenel.min.js");
	foreach($javaScriptMinIncludes as $script)
	{
?>
	<script src="<?php print ROOT_FOLDER; ?>js/<?=$script?>"></script>
<?php
	}

	$javaScriptSrcIncludes = array("ravenel.js");
	foreach($javaScriptSrcIncludes as $script)
	{
?>
	<script src="<?php print ROOT_FOLDER; ?>js/src/<?=$script?>"></script>
<?php
	}
?>
	<script src="//www.google.com/recaptcha/api.js"></script>
<?php
	//The following scripts and libraries are to be used only for the Correspondence Map on the map page.
	if(strpos($application->getTitle(), "Correspondence") !== false && strpos($application->getTitle(), "Map") !== false)
	{
?>
<script>
	var dojoConfig = {
		packages: [
			{name: 'app', location: '<?php print ROOT_FOLDER; ?>js/letter/app'},
			{name: 'mainJs', location: '<?php print ROOT_FOLDER; ?>js/letter'},
			{name: 'ext', location: '<?php print ROOT_FOLDER; ?>js/letter/app/ext'}
		]
	};
	</script>
	<script src="<?php print ROOT_FOLDER; ?>js/letter/vendor/knockout-3.4.0.js"></script>
	<script src="//js.arcgis.com/3.15/"></script>
	<script src="<?php print ROOT_FOLDER; ?>js/letter/vendor/terraformer.min.js"></script>
	<script src="<?php print ROOT_FOLDER; ?>js/letter/vendor/terraformer-arcgis-parser.min.js"></script>
	<script>
	require(['dojo/parser', 'mainJs/main', 'dojo/domReady!'], function (parser, main) {
		parser.parse();
		main();
	});
	</script>
<?php
	}
?>
</body>
</html>
