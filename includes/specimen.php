<?php
/**
 * @file   includes/specimen.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Specimen Item Class.
 *
 * NOTE: Assure that every MySQL statement is checking that the collection code is
 * set to 'HWR'. Other columns set to 'IS NOT NULL' is pure opinion-based.
 */

class Specimen extends Exception {
  private $data;
  private $name;
  private $image;
  private $thumb;
  private $search;
  private $catalog;

  /**
   * Initialization Constructor.
   *
   * @param string $catalog
   *   The catalog number of the specimen.
   */
  public function __construct($catalog) {
    global $mysqli;

    // Sanitize.
    $catalog = $mysqli->real_escape_string($catalog);

    // Retrieve data.
    $statement = $mysqli->prepare("SELECT omoccurrences.scientificName, omoccurrences.eventDate, omoccurrences.habitat, omoccurrences.family, omoccurrences.locality, omoccurrences.county, omoccurrences.stateProvince, omoccurrences.country, omoccurrences.identifiedBy, omoccurrences.recordedBy, omoccurrences.cultivationStatus, omoccurrences.decimalLatitude, omoccurrences.decimalLongitude, images.thumbnailurl, images.url FROM images, omoccurrences WHERE omoccurrences.collectionCode = 'HWR' AND images.occid = omoccurrences.occid AND omoccurrences.otherCatalogNumbers = ? AND images.thumbnailurl IS NOT NULL AND images.url IS NOT NULL LIMIT 1");
    $statement->bind_param("s", $catalog);
    $statement->execute();
    $statement->store_result();

    if ($statement->num_rows === 0) {
      throw new Exception("The catalog number '" . $catalog . "' does not exist within the database. Please try again.");
    } // if ($statement->num_rows === 0)

    $statement->bind_result($scientificName, $eventDate, $habitat, $family, $locality, $county, $stateProvince, $country, $identifiedBy, $recordedBy, $cultivationStatus, $decimalLatitude, $decimalLongitude, $thumbnailurl, $url);
    $statement->fetch();

    // Assign data.
    $this->name  = $scientificName;
    $this->image = $url;
    $this->thumb = $thumbnailurl;

    $this->data["family"]            = trim($family);
    $this->data["habitat"]           = trim($habitat);
    $this->data["location"]          = substr(str_replace(", ,", "", trim($locality) . ", " . trim($county) . ", " . trim($stateProvince) . ", " . trim($country) . ", "), 0, -2);
    $this->data["eventDate"]         = trim($eventDate);
    $this->data["recordedBy"]        = trim($recordedBy);
    $this->data["coordinates"]       = trim($decimalLatitude) === "" ? "" : trim($decimalLatitude) . ", " . trim($decimalLongitude);
    $this->data["identifiedBy"]      = trim($identifiedBy);
    $this->data["cultivationStatus"] = trim($cultivationStatus);

    $statement->close();

    // Initialize the rest of the variables.
    $this->search  = "";
    $this->catalog = $catalog;
  } // public function __construct($catalog)

  /**
   * Metadata Renderer.
   *
   * @return string
   *   The HTML for the list group.
   */
  public function renderViewerMetadata() {
    global $application;

    $metadata = $application->renderListItem($this->name, "scientificName", $this->search);
    foreach (array("eventDate", "habitat", "family", "location", "coordinates", "identifiedBy", "recordedBy", "cultivationStatus") as $item) {
      $metadata .= $application->renderListItem($this->data[$item], $item, $this->search);
    } // foreach (array("eventDate", "habitat", "family", "location", "coordinates", "identifiedBy", "recordedBy", "cultivationStatus") as $item)

    return '<ul class="list-group">' . $metadata . '</ul>';
  } // public function renderViewerMetadata()

  /**
   * Similar Specimen Renderer.
   *
   * In the instance that more than one specimen has the same name as the one that is
   * being viewed, display them as an option for the user to view.
   *
   * @return string
   *   The HTML for the similar specimens section.
   */
  public function renderSimilarSpecimens() {
    global $mysqli;

    // Sanitize.
    $this->name    = $mysqli->real_escape_string($this->name);
    $this->catalog = $mysqli->real_escape_string($this->catalog);

    // Retrieve results.
    $statement = $mysqli->prepare("SELECT images.thumbnailurl, omoccurrences.otherCatalogNumbers FROM images, omoccurrences WHERE omoccurrences.collectionCode = 'HWR' AND omoccurrences.scientificName = ? AND omoccurrences.otherCatalogNumbers != ? AND omoccurrences.occid = images.occid AND images.url IS NOT NULL");
    $statement->bind_param("ss", $this->name, $this->catalog);
    $statement->execute();
    $statement->store_result();
    $statement->bind_result($thumbnailurl, $otherCatalogNumbers);

    // Return nothing if there's nothing.
    if ($statement->num_rows === 0) {
      return "";
    } // if ($statement->num_rows === 0)

    // Append un-nothing if there's un-nothing.
    $results = "";
    while ($statement->fetch()) {
      $results .= '<div class="text-center"><a href="' . ROOT_FOLDER . 'viewer?type=specimen&institute=Carolina&number=' . $otherCatalogNumbers . '/"><img src="' . $thumbnailurl . '" class="img-responsive center-block" alt="Catalog #' . $otherCatalogNumbers . '"></a></div>';
    } // while ($statement->fetch())

    $statement->close();

    return '<div class="col-md-6"><h4>Other Specimen Variations of ' . $this->name . '</h4><div data-effect="slick" data-slick="on" data-slick-amount="3">' . $results . '</div></div>';
  } // public function renderSimilarSpecimens()

  /**
   * Journal Mention Renderer.
   *
   * In the instance that the specimen that is being viewed was also mentioned in a
   * journal by Ravenel, display the links as an option for the user.
   *
   * @return string
   *   The HTML for the journal mentions section.
   */
  public function renderJournalMentions() {
    $results     = array_merge_recursive(json_decode(file_get_contents("http://digitalcollections.clemson.edu:81/dmwebservices/index.php?q=dmQuery/rvl/scient^" . str_replace(" ", "^any!and^scient^", $this->name) . "^any/title/0/1024/0/0/0/0/0/1/json"), true), json_decode(file_get_contents("http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmQuery/rav/scient^" . str_replace(" ", "^any!and^scient^", $this->name) . "^any/title/0/1024/0/0/0/0/0/1/json"), true));
    $manuscripts = array();

    foreach ($results["records"] as $record) {
      array_push($manuscripts, new Manuscript($record["pointer"], $record["collection"] === "/rav" ? "Carolina" : "Clemson"));
    } // foreach ($results["records"] as $record)

    if (empty($manuscripts)) {
      return "";
    } // if (empty($manuscripts))

    $output = "";
    foreach ($manuscripts as $manuscript) {
      $output .= '<div class="text-center" data-pointer="' . $manuscript->getPointer() . '" data-institute="' . $manuscript->getInstitute() . '" style="cursor: pointer;"><img src="' . $manuscript->getThumb() . '" class="img-responsive center-block" alt="Pointer ' . $manuscript->getPointer() . '">';
      $title   = $manuscript->getData("title");

      if (strpos($title, "Private Journal") !== false && strpos($title, "Page") !== false) {
        $output .= '<span class="text-muted" style="display: block;">' . substr($title, strpos($title, "Private Journal"), strpos($title, "Page") - strpos($title, "Private Journal")) . '</span><span class="text-muted" style="display: block;">' . substr($title, strpos($title, "Page"), strlen($title)) . '</span>';
      } else {
        $output .= '<span class="text-muted" style="display: block;">' . $title . '</span>';
      } // if (strpos($title, "Private Journal") !== false && strpos($title, "Page") !== false)

      $output .= '</div>';
    } // foreach ($manuscript as $manuscript)

    return '<div class="col-md-6" id="specimenJournalMentions"><h4>Journals that Mention ' . $this->name . '</h4><div data-effect="slick" data-slick="on" data-slick-amount="3">' . $output . '</div></div>';
  } // public function renderJournalMentions()

  /**
   * @param  string $key
   *   The metadata field key.
   * @return string
   *   The metadata field value.
   */
  public function getData($key) {
    if (array_key_exists($key, $this->data)) {
      return empty($this->data[$key]) ? "" : $this->data[$key];
    } else if ($key === "locality") {
      return $this->data["location"];
    } // if (array_key_exists($key, $this->data))

    return "";
  } // public function getData($key)

  /**
   * Accessors.
   */
  public function getName() {
    return $this->name;
  } // public function getName()

  public function getImage() {
    return $this->image;
  } // public function getImage()

  public function getThumb() {
    return $this->thumb;
  } // public function getThumb()

  public function getSearch() {
    return $this->search;
  } // public function getSearch()

  public function getCatalog() {
    return $this->catalog;
  } // public function getCatalog()

  /**
   * Mutators.
   */
  public function setData($data) {
    $this->data = $data;
  } // public function setData($data)

  public function setName($name) {
    $this->name = $name;
  } // public function setName($name)

  public function setImage($image) {
    $this->image = $image;
  } // public function setImage($image)

  public function setThumb($thumb) {
    $this->thumb = $thumb;
  } // public function setThumb($thumb)

  public function setSearch($search) {
    $this->search = $search;
  } // public function setSearch($search)

  public function setCatalog($catalog) {
    $this->catalog = $catalog;
  } // public function setCatalog($catalog)
} // class Specimen
