<?php
/**
 * @file   includes/search-results.php
 * @author Collin Haines - Center for Digital Humanities
 */

require "configuration.php";

class SearchResults {
  private $search;
  private $inputs;
  private $options;
  private $operators;

  private $clemsonQuery;
  private $carolinaQuery;

  private $letters;
  private $journals;
  private $specimens;

  /**
   * Constructor
   *
   * Basic constructor that initializes class-wide variables.
   */
  public function __construct() {
    $this->search    = "";
    $this->inputs    = array();
    $this->options   = array();
    $this->operators = array("and");

    $this->clemsonQuery  = "";
    $this->carolinaQuery = "";

    $this->letters   = array();
    $this->journals  = array();
    $this->specimens = array();
  } // public function __construct()

  /**
   * Manuscript Query Creator
   *
   * An algorithm to determine what needs to be parsed together to create a query for
   * CONTENTdm and retrieve at least one result.
   *
   * @param  integer $counter
   *   The n-th time this was executed.
   * @param  string  $instituteToSkip
   *   Used if we already have a living query from an institute.
   */
  public function createManuscriptQueries($counter = 0, $instituteToSkip = "") {
    $field = array();
    $input = array();

    if ($this->search === "") {
      $input = $this->inputs;
      $field = $this->options;
    } else if (empty($this->inputs) && empty($this->options)) {
      $explode = explode(" ", str_replace(" and ", " ", $this->search));

      if (!isset($_GET["browse"]) || (isset($_GET["browse"]) && count($explode) < 7)) {
        $input = $explode;
      } else if (isset($_GET["browse"])) {
        $input = array_fill(0, 1, $this->search);
      } // if (!isset($_GET["browse"]) || (isset($_GET["browse"]) && count($explode) < 7))

      $field = array_fill(0, count($input), isset($_GET["browse"]) ? $_GET["browse"] : "CISOSEARCHALL");
    } // if ($this->search === "")

    $logic = $this->search === "" ? $this->operators : array_fill(0, count($input), "and");

    $center = "";
    for ($i = 0; $i < count($input); $i++) {
      if ($i === 6) { break; }

      if (0 < $i) {
        $center .= "^" . $logic[$i - 1] . "!";
      } // if (0 < $i)

      if (2 <= $counter && $i === 0 && ($i + 1) === count($input)) {
        // There's one word and this is the second time this has been ran.
        $center .= $field[$i] . "^*" . $input[$i] . "*^any";
      } else if (($counter === 1 || 2 <= $counter) && ($i + 1) === count($input)) {
        // There needs to be an asterisk on the end of the last search term.
        $center .= $field[$i] . "^" . $input[$i] . "*^any";
      } else if (2 <= $counter && $i === 0) {
        // There needs to be an asterisk on the start of the first search term.
        $center .= $field[$i] . "^*" . $input[$i] . "^any";
      } else {
        // There does not need to be an asterisk anywhere.
        $center .= $field[$i] . "^" . $input[$i] . "^any";
      } // if ($counter === 2 && $i === 0 && ($i + 1) === count($input))
    } // for ($i = 0; $i < count($input); $i++)

    if ($instituteToSkip !== "Clemson") {
       $this->clemsonQuery = "http://culcdm.clemson.edu:81/dmwebservices/index.php?q=dmQuery/rvl/" . str_replace("people^", "creato^", str_replace("latitt^", "latitu^", $center)) . "/common!creato!date!descri!geogra!latitu!scient!title!transc/0/1024/0/0/0/0/0/1/json";    
  
#$this->clemsonQuery = "http://digitalcollections.clemson.edu:81/dmwebservices/index.php?q=dmQuery/rvl/" . str_replace("people^", "creato^", str_replace("latitt^", "latitu^", $center)) . "/common!creato!date!descri!geogra!latitu!scient!title!transc/0/1024/0/0/0/0/0/1/json";
    } // if ($instituteToSkip !== "Clemson")

    if ($instituteToSkip !== "Carolina") {
      $this->carolinaQuery = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmQuery/rav/" . $center . "/common!date!descri!geogra!lattit!people!scient!title!transc/0/1024/0/0/0/0/0/1/json";
    } // if ($instituteToSkip !== "Carolina")
  } // public function createManuscriptQueries($counter = 0, $instituteToSkip = "")

  /**
   * Organizer.
   *
   * Determines if the initial query to each institution returns results. If not, try
   * again for a maximum of three times. Once completed, prepare to split the data.
   */
  public function organizeManuscriptResults() {
    $clemson  = array();
    $carolina = array();

    $skipClemson  = false;
    $skipCarolina = false;

    for ($i = 1; $i < 4; $i++) {
      if (!$skipClemson) {
        $clemson = json_decode(file_get_contents($this->clemsonQuery), true);
      } // if (!$skipClemson)

      if (!$skipCarolina) {
        $carolina = json_decode(file_get_contents($this->carolinaQuery), true);
      } // if (!$skipCarolina)

      if (empty($clemson["records"])) {
        $this->createManuscriptQueries($i, "Carolina");
      } else {
        $skipClemson = true;
      } // if (empty($clemson["records"]))

      if (empty($carolina["records"])) {
        $this->createManuscriptQueries($i, "Clemson");
      } else {
        $skipCarolina = true;
      } // if (empty($carolina["records"]))

      if (!empty($clemson["records"]) && !empty($carolina["records"])) { break; }
    } // for ($i = 0; $i < 4; $i++)

    $this->splitData($clemson["records"]);
    $this->splitData($carolina["records"]);
  } // public function organizeManuscriptResults()

  /**
   * Splitter
   *
   * Runs through every record given, parses or replaces certain data, and then puts
   * itself into its correct category array, standing by until the data is ready to
   * be sent back to the client.
   *
   * @param  array $records
   *   An array of records returned from CONTENTdm that is our search results.
   */
  private function splitData($records) {
   if (is_array($records) || is_object($records))
   {
    foreach ($records as $record) {
      // Detect if it is an image. Skip if so.
      if (9088 < $record["pointer"] && $record["pointer"] < 9154) { continue; }

      // Remove unnecessary metadata returned from the search results.
      unset($record["collection"], $record["filetype"], $record["parentobject"], $record["find"]);

      // Adjust the pointer to have a link.
      $link  = $record["pointer"] < 3000 ? 'Clemson' : 'Carolina';
      $link .= '/' . $record["pointer"] . '/' . urlencode(str_replace(" ", "-", $this->search === "" ? implode(" ", $this->inputs) : $this->search));
      $image = $record["pointer"] < 3000 ? 'http://culcdm.clemson.edu/utils/getthumbnail/collection/rvl/id/' : 'http://digital.tcl.sc.edu/utils/getthumbnail/collection/rav/id/';     
#$image = $record["pointer"] < 3000 ? 'http://digitalcollections.clemson.edu/utils/getthumbnail/collection/rvl/id/' : 'http://digital.tcl.sc.edu/utils/getthumbnail/collection/rav/id/';

      $record["pointer"] = '<a href="' . ROOT_FOLDER . 'viewer/transcript/' . $link . '/"><img src="' . $image . $record["pointer"] . '" class="img-responsive" alt="' . $record["title"] . '"></a>';

      // Clemson is different. Here at USC, we accept it.
      if (isset($record["creato"])) {
        $record["people"] = $record["creato"];

        unset($record["creato"]);
      } // if (isset($record["creato"]))

      if (isset($record["latitu"])) {
        $record["lattit"] = $record["latitu"];

        unset($record["latitu"]);
      } // if (isset($record["latitu"]))

      // Render proper HTML.
      foreach ($record as $key=>$value) {
        if ($key === "pointer" || $key === "title") { continue; }

        $record[$key] = $this->renderTableDataCell($value, $key);
      } // foreach ($record as $key=>$value)

      // Move the record into its correct category.
      if ($this->isLetter($record["title"])) {
        array_push($this->letters, $record);
      } else {
        array_push($this->journals, $record);
      } // if ($this->isLetter($record["title"]))
    } // foreach ($records as $record)
  } // if (is_array($records) || is_object($records)) 
} // private function splitData($records)

  /**
   * Data Cell Interior Renderer.
   *
   * @param  object $data
   *   The manuscript object's current data value.
   * @param  string $type
   *   The manuscript object's current data type.
   * @return string
   *   The HTML structure.
   */
  private function renderTableDataCell($data, $type) {
    global $application;

    if ((gettype($data) === "array" && empty($data)) || (gettype($data) === "string" && trim($data) === "")) {
      return "<em>Unknown or N/A</em>";
    } else if ($type === "transc") {
      return "<pre>" . $application->highlightSearchTerm($data, $this->search === "" ? implode(" ", $this->inputs) : $this->search) . "</pre>";
    } // if ((gettype($data) === "array" && empty($data)) || (gettype($data) === "string" && trim($data) === ""))

    $list = "";
    foreach ($type === "date" ? $application->convertDate($data) : explode(";", $data) as $item) {
      $list .= "<li>" . $application->highlightSearchTerm($item, $this->search === "" ? implode(" ", $this->inputs) : $this->search) . "</li>";
    } // foreach ($application->startsWith($data, "1") ? $application->convertDate($data) : explode(";", $data) as $item)

    return '<ul class="list-unstyled">' . $list . "</ul>";
  } // private function renderTableDataCell($data, $type)

  /**
   * Population
   *
   * Retrieves the search results from Symbiota, our specimen database. Black magic
   * is used in the first part to assure that there are no mistakes in parameter
   * populating. Once completed, parse the data and have it idle until it is ready to
   * be sent back to the client.
   */
  public function populateSpecimenData() {
    global $mysqli;
    #echo var_dump($mysqli);
    $keys = array("eventDate", "identifiedBy", "locality", "county", "stateProvince", "country", "decimalLatitude", "decimalLongitude", "habitat", "recordedBy", "cultivationStatus");

    // Create the MySQL query.
    $liker = "omoccurrences.scientificName LIKE CONCAT('%', ?, '%')";
    $query = "images.thumbnailurl, omoccurrences.otherCatalogNumbers, omoccurrences.scientificName";

    foreach ($keys as $key) {
      $liker .= " OR omoccurrences." . $key . " LIKE CONCAT('%', ?, '%')";
      $query .= ", omoccurrences." . $key;
    } // foreach ($keys as $key)

    $database = "SELECT $query FROM images, omoccurrences WHERE omoccurrences.otherCatalogNumbers IS NOT NULL AND omoccurrences.collectionCode = 'HWR' AND images.occid = omoccurrences.occid AND ($liker)";
    
  // Test Code   ----- START

   
//    echo "<script>console.log( 'Debug Objects: " . $database . "' );</script>";

// ---- END


    $statement = $mysqli->prepare($database);

    // Declare the dynamic number of parameters to be binded.
    // NOTE: Add 1 for the "scientificName" in the $liker initialization.
    $names[]    = implode("", array_fill(0, count($keys) + 1, "s"));
    $parameters = array_fill(0, count($keys) + 1, $this->search === "" ? implode(" ", $this->inputs) : $this->search);

    for ($i = 0; $i < count($parameters); $i++) {
      $name = "bind" . $i;
      $$name = $mysqli->real_escape_string($parameters[$i]);
      $names[] = &$$name;
    } // for ($i = 0; $i < count($parameters); $i++)

    call_user_func_array(array($statement, "bind_param"), $names);

    // Execute the query.
    $statement->execute();

    // Grab the dynamic number of results.
    $statement->store_result();

    // Store the result.
    $statement->bind_result($thumbnailurl, $otherCatalogNumbers, $scientificName, $eventDate, $identifiedBy, $locality, $county, $stateProvince, $country, $decimalLatitude, $decimalLongitude, $habitat, $recordedBy, $cultivationStatus);

    $search = urlencode(str_replace(" ", "-", $this->search === "" ? implode(" ", $this->inputs) : $this->search));

    // Run through all data.
    while ($statement->fetch()) {
      $data = array(
        "thumbnailurl"      => '<a href="' . ROOT_FOLDER . 'viewer/specimen/Carolina/' . trim($otherCatalogNumbers) . '/' . $search . '/"><img src="' . trim($thumbnailurl) . '" class="img-responsive" alt="' . trim($scientificName) . '"></a>',
        "scientificName"    => trim($scientificName),
        "eventDate"         => trim($eventDate),
        "identifiedBy"      => trim($identifiedBy),
        "location"          => substr(str_replace(", ,", "", trim($locality) . ", " . trim($county) . ", " . trim($stateProvince) . ", " . trim($country) . ", "), 0, -2),
        "coordinates"       => trim($decimalLatitude) === "" ? "" : trim($decimalLatitude) . ", " . trim($decimalLongitude),
        "habitat"           => trim($habitat),
        "recordedBy"        => trim($recordedBy),
        "cultivationStatus" => trim($cultivationStatus)
      );

      // Render proper HTML.
      foreach ($data as $key=>$value) {
        if ($key === "thumbnailurl") { continue; }

        $data[$key] = $this->renderTableDataCell($value, $key);
      } // foreach ($data as $key=>$value)

      array_push($this->specimens, $data);
    } // while ($statement->fetch())

    $statement->close();
  } // public function populateSpecimenData()

  /**
   * Scanner
   *
   * Runs through all known pointers that are images and matches the user search
   * against the title of the item.
   *
   * @return Array
   */
  public function scanImageEntries() {
    $array = array("images" => array());

    foreach (range(9089, 9149) as $pointer) {
      $info = json_decode(file_get_contents("http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo/rav/" . $pointer . "/id/json"), true);

      if (stripos($info["title"], $this->search) !== false) {
        array_push($array["images"], '<div class="col-xs-6"><a href="' . ROOT_FOLDER . 'viewer/transcript/Carolina/' . $pointer . '/' . $this->search . '/"><img src="' . ROOT_FOLDER . 'img/gallery-small/' . $pointer . '.jpg" class="img-responsive"><p class="text-center">View Image</p></a></div>');
      } // if (stripos($info["title"], $this->search) !== false)
    } // foreach (range(9089, 9149) as $pointer)

    return $array;
  } // public function scanImageEntries()

  /**
   * Letter Determiner
   *
   * Determines based on a pre-set list of keywords if the given title is a letter.
   *
   * @param  string  $title
   *   The title of the manuscript.
   * @return boolean
   */
  private function isLetter($title) {
    foreach (array("Card", "Certificate", "Circular", "Classification", "Deed", "Envelope", "Essay", "Letter", "List", "Manual", "Note", "Postcard", "Receipt", "Report", "Statement", "Testament") as $keyword) {
      if (stripos($title, $keyword) !== false) {
        return true;
      } // if (stripos($title, $keyword) !== false)
    } // foreach (array("Card", "Certificate", "Circular", "Classification", "Deed", "Envelope", "Essay", "Letter", "List", "Manual", "Note", "Postcard", "Receipt", "Report", "Statement", "Testament") as $keyword)

    return false;
  } // private function isLetter($title)

  /**
   * Accessors.
   */
  public function getLetters() {
    return $this->letters;
  } // public function getLetters()

  public function getJournals() {
    return $this->journals;
  } // public function getJournals()

  public function getSpecimens() {
    return $this->specimens;
  } // public function getSpecimens()

  /**
   * Mutators.
   */
  public function setSearch($search) {
    $this->search = $search;
  } // public function setSearch($search)

  public function setInputs($inputs) {
    $this->inputs = $inputs;
  } // public function setInputs($inputs)

  public function setOptions($options) {
    $this->options = $options;
  } // public function setOptions($options)

  public function setOperators($operators) {
    $this->operators = $operators;
  } // public function setOperators($operators)
} // class SearchResults

$search = new SearchResults();

if (isset($_GET["search"])) {
  $search->setSearch($_GET["search"]);
} else if (isset($_GET["options"], $_GET["input"])) {
  $search->setInputs($_GET["input"]);
  $search->setOptions($_GET["options"]);

  if (isset($_GET["operators"])) {
    $search->setOperators($_GET["operators"]);
  } // if (isset($_GET["operators"]))
} else {
  header("Location: /ravenel/search");
  exit;
} // if (isset($_GET["search"]))

if ($_GET["type"] === "journals" || $_GET["type"] === "letters") {
  $search->createManuscriptQueries();
  $search->organizeManuscriptResults();

  $data = $_GET["type"] === "letters" ? $search->getLetters() : $search->getJournals();

  exit(json_encode(array("recordsTotal" => count($data), "recordsFiltered" => count($data), "data" => $data)));
} else if ($_GET["type"] === "specimens") {
  $search->populateSpecimenData();

  exit(json_encode(array("recordsTotal" => count($search->getSpecimens()), "recordsFiltered" => count($search->getSpecimens()), "data" => $search->getSpecimens())));
} else if ($_GET["type"] === "photographs") {
  exit(json_encode($search->scanImageEntries()));
} // if ($_GET["type"] === "journals" || $_GET["type"] === "letters")
