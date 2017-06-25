<?php
/**
 * @file
 * fungi.php
 */

require_once "includes/configuration.php";

$application->setTitle("Fungi Caroliniani Exsiccati");

$query = "
SELECT   parent_object, pointer, title
FROM     manuscripts
WHERE    pointer = '9705'
         OR pointer = '9232'
         OR pointer = '9307'
         OR pointer = '9461'
         OR pointer = '9540'
         OR pointer = '8128'
ORDER BY pointer ASC
";

// http://digital.tcl.sc.edu/cdm/search/collection/rav/searchterm/Fungi%20Caroliniana%20Exsicatti,%20Century/field/title/mode/any/conn/and/order/nosort/ad/asc/cosuppress/1

$prepare = $application->getConnection()->prepare($query);

$prepare->execute();

$results = $prepare->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

require "layout/header.php";
?>
<main class="container">
  <div class="row page-header">
    <div class="col-xs-12">
      <h1>Fungi of Carolina</h1>

      <p class="lead">Illustrated By Natural Specimens of the Species</p>
    </div>
  </div>

  <div class="row">
    <?php foreach ($results as $key=>$result): ?>
      <?php
        $info  = json_decode(file_get_contents($application->getManuscriptCompoundObjectInfo($result['pointer'])), true);
        $first = $info['page'][0]['pageptr'];

        $query = "
        SELECT image_height, image_width
        FROM   manuscripts
        WHERE  pointer = :pointer
        ";

        $prepare = $application->getConnection()->prepare($query);
        $prepare->bindParam(':pointer', $first);
        $prepare->execute();

        $image = $prepare->fetchObject();
      ?>
      <a href="<?php print ROOT_FOLDER; ?>viewer?type=transcript&institute=Carolina&number=<?php print $first; ?>">
        <div class="col-sm-4">
          <h2><?php print $result["title"]; ?></h2>

          <img src="<?php print $application->getManuscriptImage($first, $image->image_width, $image->image_height); ?>" alt="<?php print $result["title"]; ?>" class="img-responsive">
        </div>
      </a>

      <?php $count++; ?>

      <?php if ($count % 3 === 0): ?>
        </div>
        <hr>
        <div class="row">
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</main>
<?php require "layout/footer.php"; ?>
