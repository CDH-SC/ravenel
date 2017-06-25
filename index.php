<?php
/**
 * @file   index.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Renders the home screen.
 */

require_once "includes/configuration.php";

require "layout/header.php";
?>
<article class="features">
  <div class="container">
    <div class="row feature">
      <div class="col-md-3">
        <img src="<?php print ROOT_FOLDER; ?>img/features/young_ravenel_square.png" class="img-responsive center-block" alt="Henry William Ravenel at a Young Age">
      </div>

      <div class="col-md-9">
        <h1 class="feature-header">Henry William Ravenel</h1>

        <p class="feature-text">Henry William Ravenel (1814-1887), a botanist, planter, and author documented over 6,200 botanical specimens and wrote over 3,000 pages of field journals and correspondence. For the first time, Ravenel's botanical collection, thirteen journals (1859-1887), and over 400 letters between him and other renowned naturalists and family members across the country and world have been digitized and can be read and searched together in one place.</p>

        <p class="feature-text">
          <a href="<?php print ROOT_FOLDER; ?>about">Learn More About Henry William Ravenel</a>
        </p>
      </div>
    </div>
  </div>
</article>

<article class="services">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-3 col-sm-6 col-md-spacing">
        <a href="<?php print ROOT_FOLDER; ?>search" class="service-container">
          <i class="fa fa-search fa-3x"></i>

          <h3>Search</h3>

          <p>Search through three different databases that house information about Ravenel's experiences and findings.</p>
        </a>
      </div>

      <div class="col-md-3 col-sm-6 col-md-spacing">
        <a href="<?php print ROOT_FOLDER; ?>browse" class="service-container">
          <i class="fa fa-list-alt fa-3x"></i>

          <h3>Browse</h3>

          <p>Browse through keywords of Ravenel's journals that involve the date, people, places, and plants he mentioned.</p>
        </a>
      </div>

      <?php
      $random = rand(0, 1);

      if ($random === 0) {
        $link = "type=transcript&institute=Carolina&number=" . $application->randomManuscript();
      } else {
        $link = "type=specimen&institute=Carolina&number=" . $application->randomSpecimen();
      }
      ?>

      <div class="col-md-3 col-sm-6 col-sm-spacing">
        <a href="<?php print ROOT_FOLDER; ?>viewer?<?php print $link; ?>" class="service-container">
          <i class="fa fa-binoculars fa-3x"></i>

          <h3>Viewer</h3>

          <p>Have a side-by-side view of an original journal entry and the text transcription or a specimen and its attributes.</p>
        </a>
      </div>

      <?php
      /*
       * Create a random map item.
       *
       * Possible Viewing:
       * 0 => Travel Map
       * 1 => Correspondence Map
       * 2 => Plant Specimens Map
       */

      $select = rand(0, 2);

      if ($select === 0) {
        $map = "travel";
      } else if ($select === 1) {
        $map = "letters";
      } else {
        $map = "plants";
      }
      ?>

      <div class="col-md-3 col-sm-6">
        <a href="<?php print ROOT_FOLDER; ?>map?type=<?php print $map; ?>" class="service-container">
          <i class="fa fa-flag fa-3x"></i>

          <h3>Map</h3>

          <p>View where Ravenel wrote to colleagues and friends, collected plants, and how he traveled 5,000 miles in 2 months.</p>
        </a>
      </div>
    </div>
  </div>
</article>

<article class="features">
  <div class="container">
    <div class="row feature">
      <div class="col-md-9">
        <h1 class="feature-header">Journals and Manuscripts</h1>

        <p class="feature-text">From a snowy Christmas in Aiken, SC through the beginning and ending of the American Civil War and a trip to Texas, Ravenel's journals, housed at USC's South Caroliniana Library, illuminate his experiences during all types of historical events. The rich collection of letters, discussing the sharing and discovering of new plants as well as family issues and estate dealings, have been brought together from the South Caroliniana Library, Clemson University, Converse College, and the University of North Carolina Wilson Library, which contributed a portion of the Moses A. Curtis Papers, a close friend of Ravenel's.</p>

        <p class="feature-text">
          <a href="<?php print ROOT_FOLDER; ?>search">Search Manuscripts</a>
        </p>
      </div>

      <div class="col-md-3">
        <img src="<?php print ROOT_FOLDER; ?>img/features/journal_square.png" class="img-responsive center-block" alt="Henry William Ravenel, 1814-1887: Private Journal 1860-1861: Page 16">
      </div>
    </div>
  </div>
</article>

<article class="stats text-center">
  <div class="container">
    <div class="row">
      <div class="col-sm-4 col-sm-spacing">
        <i class="fa fa-envelope fa-3x"></i>

        <h3>Letters</h3>

        <?php
        $prepare = $application->getConnection()->prepare("
          SELECT COUNT(pointer)
          FROM   manuscripts
          WHERE  title ILIKE '%card%'
          OR     title ILIKE '%certificate%'
          OR     title ILIKE '%circular%'
          OR     title ILIKE '%classification%'
          OR     title ILIKE '%deed%'
          OR     title ILIKE '%envelope%'
          OR     title ILIKE '%essay%'
          OR     title ILIKE '%letter%'
          OR     title ILIKE '%list%'
          OR     title ILIKE '%manual%'
          OR     title ILIKE '%note%'
          OR     title ILIKE '%postcard%'
          OR     title ILIKE '%receipt%'
          OR     title ILIKE '%report%'
          OR     title ILIKE '%statement%'
          OR     title ILIKE '%testament%'
        ");

        $prepare->execute();
        ?>

        <span class="stat-counter" data-effect="count"><?php print $prepare->fetchColumn(); ?></span>
      </div>

      <div class="col-sm-4 col-sm-spacing">
        <i class="fa fa-leaf fa-3x"></i>

        <h3>Plant Specimens</h3>

        <?php
        $prepare = $application->getConnection()->prepare("
          SELECT COUNT(id)
          FROM   plants
        ");

        $prepare->execute();
        ?>

        <span class="stat-counter" data-effect="count"><?php print $prepare->fetchColumn(); ?></span>
      </div>

      <div class="col-sm-4">
        <i class="fa fa-book fa-3x"></i>

        <h3>Journals</h3>

        <span class="stat-counter" data-effect="count">13</span>
      </div>
    </div>
  </div>
</article>

<article class="features">
  <div class="container">
    <div class="row feature">
      <div class="col-md-3">
        <img src="<?php print ROOT_FOLDER; ?>img/features/plant_specimen_square.png" class="img-responsive center-block" alt="Helianthus angustifolius">
      </div>

      <div class="col-md-9">
        <h1 class="feature-header">Plant Specimens</h1>

        <p class="feature-text">Referring to his herbarium as "the labors of my life time," Ravenel had amassed "some 10 to 12 thousand species of plants altogether." (<a href="<?php print ROOT_FOLDER; ?>viewer?type=transcript&institute=Carolina&number=6945">HWR Journal 1884-1887 Page 32</a>) After portions of the collection were sold to the British Museum and Biltmore Estate, the surviving portion (approximately 6,200 specimens) was held at Converse College for teaching purposes. In 2004, the collection was transferred to the A. C. Moore Herbarium at the University of South Carolina where it has been restored and digitized.</p>

        <p class="feature-text">
          <a href="<?php print ROOT_FOLDER; ?>map?type=plants">View Map of Collected Plant Specimens</a>
        </p>
      </div>
    </div>
  </div>
</article>
<?php require "layout/footer.php"; ?>
