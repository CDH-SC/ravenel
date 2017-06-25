<?php
/**
 * @file   includes/manuscript.php
 * @author Collin Haines - Center for Digital Humanities
 */

class Manuscript extends Exception {
  private $data;
  private $image;
  private $thumb;
  private $pointer;
  private $institute;

  private $alias;
  private $domain;
  private $search;
  private $compound;
  private $isClemson;

  /**
   * Constructor.
   *
   * @param string $pointer
   *   The manuscript pointer.
   * @param string $institute
   *   The institution the manuscript lives in.
   */
  public function __construct($pointer, $institute) {
    if (strcasecmp($institute, "Clemson") !== 0 && strcasecmp($institute, "Carolina") !== 0) {
      throw new Exception("The institute \"" . $institute . "\" is not supported. Please use either \"Clemson\" or \"Carolina\".");
    } // if (strcasecmp($institute, "Clemson") !== 0 && strcasecmp($institute, "Carolina") !== 0)

    // Determine if it's Clemson. If not, assume it's USC.
    $this->isClemson = strcasecmp($institute, "Clemson") === 0;

    // Assign the CONTENTdm collection alias and domain.
    $this->alias  = $this->isClemson ? "rvl" : "rav";
    $this->domain = $this->isClemson ? "collections.clemson" : ".tcl.sc";

    // Get information.
    $this->data     = $this->getJSONQuery("dmGetItemInfo", $pointer);
    $this->compound = $this->getJSONQuery("dmGetCompoundObjectInfo", $pointer);

    // Skip if it does not exist.
    if (array_key_exists("code", $this->data)) { return; }

    // Prepare for image loading.
    $doc = new DOMDocument();

    // Fun Fact: OCLC (makers of CONTENTdm) knows that this query will only return in
    // XML format and bug out in JSON format, and they're not even working on fixing it.
    $doc->load("http://digital" . $this->domain . ".edu:81/dmwebservices/?q=dmGetImageInfo/" . $this->alias . "/" . $pointer . "/xml");

    // Grab the image. If it's a compound, grab the first image associated object.
    if (array_key_exists("page", $this->compound)) {
      if (array_key_exists("0", $this->compound["page"])) {
        $location = $this->compound["page"][0]["pageptr"];
      } else {
        $location = $this->compound["page"]["pageptr"];
      } // if (array_key_exists("0", $this->compound["page"]))
    } else {
      $location = $pointer;
    } // if (array_key_exists("page", $this->compound))

    $this->thumb = "http://digital" . $this->domain . ".edu/utils/getthumbnail/collection/" . $this->alias . "/id/" . $location;
    $this->image = "http://digital" . $this->domain . ".edu/utils/ajaxhelper/?CISOROOT=" . $this->alias . "&CISOPTR=" . $location . "&action=2&DMWIDTH=" . $doc->getElementsByTagName("width")->item(0)->nodeValue . "&DMHEIGHT=" . $doc->getElementsByTagName("height")->item(0)->nodeValue;

    // Initialize the rest of the variables.
    $this->search    = "";
    $this->pointer   = $pointer;
    $this->institute = $institute;
  } // public function __construct($pointer, $institute)

  /**
   * Metadata Renderer.
   *
   * Renders the HTML for the metadata section.
   *
   * @return string
   *   The HTML for the list group.
   */
  public function renderViewerMetadata() {
    global $application;

    // Determine keys based on institute.
    if ($this->isClemson) {
      $keys = array("title", "date", "subjec", "descri", "transc", "creato", "geogra", "scient", "common", "publis");
    } else {
      $keys = array("title", "date", "subjec", "descri", "people", "geogra", "scient", "common", "publis");
    } // if ($this->isClemson)

    $metadata = "";

    foreach ($keys as $key) {
      // Override $application->renderListItem() for side-by-side view.
      if ($key === "scient") {
        global $mysqli;

        $metadata .= '<li class="list-group-item"><h4 class="list-group-item-heading">' . $application->visualMetadata($key) . '</h4>';

        foreach (explode(";", $this->getData($key)) as $data) {
          if (trim($data) === "") {
            $metadata .= '<p class="list-group-item-text"><em>Unknown or Not Applicable</em></p>';
            break;
          } // if (trim($data) === "")

          // Sanitize.
          $data = $mysqli->real_escape_string($data);

          $statement = $mysqli->prepare("SELECT omoccurrences.otherCatalogNumbers FROM images, omoccurrences WHERE scientificName = ? AND omoccurrences.otherCatalogNumbers IS NOT NULL AND omoccurrences.collectionCode = 'HWR' AND images.occid = omoccurrences.occid AND images.url IS NOT NULL LIMIT 1");
          $statement->bind_param("s", $data);
          $statement->execute();
          $statement->store_result();

          if ($statement->num_rows === 0) {
            $metadata .= '<p class="list-group-item-text">' . $application->highlightSearchTerm($data, $this->search) . '</p>';
          } else {
            $statement->bind_result($catalog);
            $statement->fetch();

            $metadata .= '<p class="list-group-item-text viewer-specimen" data-catalog="' . $catalog . '">' . $application->highlightSearchTerm($data, $this->search) . '</p>';
          } // if ($statement->num_rows === 0)
        } // foreach (explode(";", $this->getData($key)) as $data)

        $metadata .= '</li>';
      } else {
        $metadata .= $application->renderListItem($this->getData($key), $key, $this->search);
      } // if ($key === "scient")
    } // foreach ($keys as $key)

    return '<ul class="list-group">' . $metadata . '</ul>';
  } // public function renderViewerMetadata()

  /**
   * Retrieve Siblings
   *
   * Algorithm to determine if the current item has other items related to it.
   *
   * @return string
   *   The HTML for selecting a previous and/or next page.
   */
  public function retrieveSiblings() {
    $parent = $this->getJSONQuery("GetParent", $this->pointer);

    if ($parent["parent"] != "-1") {
      $this->compound = $this->getJSONQuery("dmGetCompoundObjectInfo", $parent["parent"]);
    } // if ($parent["parent"] != "-1")

    if (array_key_exists("page", $this->compound)) {
      if (array_key_exists("0", $this->compound["page"])) {
        $total = count($this->compound["page"]) - 1;
        $place = $this->findPointerLocation($this->pointer, $this->compound);

        if ($place === 0) {
          return $this->renderSiblingLink($this->compound["page"][$place + 1]["pageptr"], "right");
        } else if (0 < $place && $place < $total) {
          return $this->renderSiblingLink($this->compound["page"][$place - 1]["pageptr"], "left") . $this->renderSiblingLink($this->compound["page"][$place + 1]["pageptr"], "right");
        } else if ($place === $total) {
          return $this->renderSiblingLink($this->compound["page"][$place - 1]["pageptr"], "left");
        } // if ($place === 0)
      } else if ($parent["parent"] != "-1") {
        return $this->renderSiblingLink($parent["parent"], "right");
      } else {
        return $this->renderSiblingLink($this->compound["page"]["pageptr"], "left");
      } // if (array_key_exists("0", $this->compound["page"]))
    } // if (array_key_exists("page", $this->compound))
  } // public function retrieveSiblings()

  /**
   * Find Pointer Location
   *
   * Finds the index of a pointer in a compound object info array.
   *
   * @param  string $pointer
   *   The pointer to look for.
   * @param  array $compound
   *   The array to search.
   * @return integer
   *   The index location.
   */
  private function findPointerLocation($pointer, $compound) {
    $count = 0;

    foreach ($compound["page"] as $k=>$array) {
      if ($array["pageptr"] == $pointer) {
        return $count;
      } // if ($array["pageptr"] == $pointer)

      $count++;
    } // foreach (compound["page"] as $k=>$array)

    return -1;
  } // private function findPointerLocation($pointer, $compound)

  /**
   * Render Sibling Links.
   *
   * Renders the HTML for a link to the current item's sibling.
   *
   * @param  string $pointer
   *   The Manuscript pointer of the sibling.
   * @param  string $direction
   *   Either left (Previous) or right (Next) direction.
   * @return string
   *   The anchor HTML.
   */
  private function renderSiblingLink($pointer, $direction) {
    $text = $direction === "left" ? "Previous" : "Next";

    return '<a href="' . ROOT_FOLDER . 'viewer?type=transcript&institute=' . $this->institute . '&number=' . $pointer . '/" class="pull-' . $direction . ' text-muted">' . $text . ' Page</a>';
  } // private function renderSiblingLink($pointer, $direction)

  /**
   * Get JSON Query
   *
   * Converts a JSON array from CONTENTdm to an array to be used
   * in this class.
   *
   * @param  string $query
   *   The specific query to ask CONTENTdm.
   * @param  string $pointer
   *   The item to look at.
   * @return array
   *   The contents.
   */
  private function getJSONQuery($query, $pointer) {
    return json_decode(file_get_contents("http://digital" . $this->domain . ".edu:81/dmwebservices/?q=" . $query . "/" . $this->alias . "/" . $pointer . "/json"), true);
  } // private function getJSONQuery($query, $pointer)

  /**
   * Data Field Accessor.
   *
   * @param  string $key
   *   The metadata field key.
   * @return string
   *   The metadata field value.
   */
  public function getData($key) {
    if ($key === "people" && $this->isClemson) {
      $key = "creato";
    } else if ($key === "creato" && !$this->isClemson) {
      $key = "people";
    } // if ($key === "people" && $this->isClemson)

    if (array_key_exists($key, $this->data)) {
      if (gettype($this->data[$key]) === "string") {
        return empty($this->data[$key]) ? "" : trim($this->data[$key]);
      } // if (gettype($this->data[$key]) === "string")
    } // if (array_key_exists($key, $this->data))

    return "";
  } // public function getData($key)

  /**
   * Accessors.
   */
  public function getImage() {
    return $this->image;
  } // public function getImage()

  public function getThumb() {
    return $this->thumb;
  } // public function getThumb()

  public function getPointer() {
    return $this->pointer;
  } // public function getPointer()

  public function getInstitute() {
    return $this->institute;
  } // public function getInstitute()

  /**
   * Mutators.
   */
  public function setData($data) {
    $this->data = $data;
  } // public function setData($data)

  public function setImage($image) {
    $this->image = $image;
  } // public function setImage($image)

  public function setThumb($thumb) {
    $this->thumb = $thumb;
  } // public function setThumb($thumb)

  public function setSearch($search) {
    $this->search = $search;
  } // public function setSearch($search)

  public function setPointer($pointer) {
    $this->pointer = $pointer;
  } // public function setPointer($pointer)

  public function setInstitute($institute) {
    $this->institute = $institute;
  } // public function setInstitute($institute)
} // class Manuscript
