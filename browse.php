<?php
/**
 * @file   browse.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Renders the browse page.
 */

$titles = array(
  "People & Organizations" => "people",
  "Plant Specimens" => "scient",
  "Locations" => "geogra",
  "Dates" => "date"
);

require_once "includes/configuration.php";

$application->setTitle("Browse");

require "layout/header.php";
?>
<div class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>Browse</h1>

      <p class="lead">All Content. One Location.</p>
    </div>
  </div>

  <div class="row hide">
    <?php foreach ($titles as $title=>$key): ?>
      <div class="col-md-3 browse-column">
        <div class="panel panel-plant">
          <div class="panel-heading">
            <h3 class="text-center"><?php print $title; ?></h3>

            <?php if ($key === "scient" || $key === "people"): ?>
              <p class="text-center" style="color: #ECECEC; font-size: 12px;">* Image Available</p>
            <?php endif; ?>
          </div>

          <div class="panel-body">
            <div class="list-group">
              <?php /* foreach ($application->convertBrowseArray($json[$key], $key) as $letter=>$value): ?>
                <button type="button" class="list-group-item"><?php print $letter; ?></button>

                <div class="list-group" style="display: none;">
                  <?php foreach ($value as $data): ?>
                    <?php if (substr($data, -1) === "*"): ?>
                      <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode(substr($data, 0, -1)); ?>&amp;browse=<?php print $key; ?>" class="list-group-item"><?php print $data; ?></a>
                    <?php elseif ($key === "date"): ?>
                      <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode($data); ?>*&amp;browse=<?php print $key; ?>" class="list-group-item"><?php print $data; ?></a>
                    <?php else: ?>
                      <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode($data); ?>&amp;browse=<?php print $key; ?>" class="list-group-item"><?php print $data; ?></a>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; */ ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row">
    <div class="col-md-3 browse-column">
      <div class="panel panel-plant">
        <div class="panel-heading">
          <h3 class="text-center">People &amp; Organizations</h3>

          <p class="text-center" style="color: #ECECEC; font-size: 12px;">* Image Available</p>
        </div>

        <?php
        $prepare = $application->getConnection()->prepare("
          SELECT   DISTINCT(people)
          FROM     manuscripts
          ORDER BY people ASC
        ");

        $prepare->execute();

        $results = $application->convertBrowse($prepare->fetchAll());
        ?>

        <div class="panel-body">
          <div class="list-group">
            <?php foreach ($results as $letter=>$array): ?>
              <button type="button" class="list-group-item"><?php print $letter; ?></button>

              <div class="list-group" style="display: none;">
                <?php foreach ($array as $key=>$value): ?>
                  <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode($value); ?>&browse=<?php print $key; ?>" class="list-group-item">
                    <?php print $value; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 browse-column">
      <div class="panel panel-plant">
        <div class="panel-heading">
          <h3 class="text-center">Plant Specimens</h3>

          <p class="text-center" style="color: #ECECEC; font-size: 12px;">* Image Available</p>
        </div>

        <?php
        $prepare = $application->getConnection()->prepare("
          SELECT   DISTINCT(scient)
          FROM     manuscripts
          ORDER BY scient ASC
        ");

        $prepare->execute();

        $results = $application->convertBrowse($prepare->fetchAll());
        ?>

        <div class="panel-body">
          <div class="list-group">
            <?php foreach ($results as $letter=>$array): ?>
              <button type="button" class="list-group-item"><?php print $letter; ?></button>

              <div class="list-group" style="display: none;">
                <?php foreach ($array as $key=>$value): ?>
                  <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode($value); ?>&browse=<?php print $key; ?>" class="list-group-item">
                    <?php print $value; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 browse-column">
      <div class="panel panel-plant">
        <div class="panel-heading">
          <h3 class="text-center">Locations</h3>
        </div>

        <?php
        $prepare = $application->getConnection()->prepare("
          SELECT   DISTINCT(geogra)
          FROM     manuscripts
          ORDER BY geogra ASC
        ");

        $prepare->execute();

        $results = $application->convertBrowse($prepare->fetchAll());
        ?>

        <div class="panel-body">
          <div class="list-group">
            <?php foreach ($results as $letter=>$array): ?>
              <button type="button" class="list-group-item"><?php print $letter; ?></button>

              <div class="list-group" style="display: none;">
                <?php foreach ($array as $key=>$value): ?>
                  <a href="<?php print ROOT_FOLDER; ?>search?search=<?php print urlencode($value); ?>&browse=<?php print $key; ?>" class="list-group-item">
                    <?php print $value; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3 browse-column">
      <div class="panel panel-plant">
        <div class="panel-heading">
          <h3 class="text-center">Dates</h3>
        </div>

        <div class="panel-body">
          <div class="list-group">

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <p>If you're unable to find an item in this page, try <a href="<?php print ROOT_FOLDER; ?>search">searching</a> for it.</p>
    </div>
  </div>
</div>
<?php require "layout/footer.php"; ?>
