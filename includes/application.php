<?php
/**
 * @file   includes/application.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Backbone functionality for the website.
 */

class Application {
  private $title;
  private $connection;

  /**
   * Initialization Constructor.
   */
  public function __construct() {
    $this->title    = "";
    $this->connection = new PDO(PSQL_CONNECTION, PSQL_USERNAME, PSQL_PASSWORD);

    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  /**
   * Word Reader.
   *
   * Determines if a word starts with a specified string.
   *
   * @param  string $haystack
   *   The complete string.
   * @param  string $needle
   *   The searching string.
   * @return boolean
   */
  public function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
  }

  /**
   * Date Converter.
   *
   * Converts a data from YYYY-MM-DD format to Month Year format.
   *
   * Example:
   *   2015-02-09  =>  February 2015
   *
   * @param  string $dates
   *   The date(s) to be converted.
   * @return array
   *   The uniquely converted date(s).
   */
  public function convertDate($dates) {
    $array = array();
    $dater = new DateTime();

    foreach (explode(";", $dates) as $date) {
      $date = trim($date);

      // Sometimes, date fields consist of the year the manuscript was inserted into CONTENTdm, skip it.
      if ($date === "" || $this->startsWith($date, "2")) { continue; }

      if (preg_match("/\d{4}-\d{2}-\d{2}/", $date)) {
        array_push($array, $dater->createFromFormat("Y-m-d", $date)->format("F Y"));
      } else if (preg_match("/\d{4}-\d{2}-\d{2}/", $date)) {
        array_push($array, $dater->createFromFormat("Y-m", $date)->format("F Y"));
      } else if (preg_match("/\d{4}/", $date)) {
        array_push($array, $date);
      }
    }

    return array_unique($array);
  }

  /**
   * Browse Converter.
   *
   * Used only for the browse page. Converts an array from an unordered array into a
   * 2D alphabetical array.
   *
   * @param  array $items
   *   The unordered array.
   * @param  string $key
   *   The type of content.
   * @return array
   *   Alphabetized array ready to render.
   */
  public function convertBrowseArray($items, $key) {
    $array = array();

    foreach ($items as $item) {
      if (is_null($item)) { continue; }

      if ($key === "date") {
        if (preg_match("/\d{4}-\d{4}/", $item)) { continue; }

        $year = substr($item, 0, 3) . "0s";

        if (!array_key_exists($year, $array)) {
          $array[$year] = array();
        }

        if (preg_match("/\d{4}-\d{2}-\d{2}/", $item)) {
          $date = date_create_from_format("Y-m-d", $item);
        } else if (preg_match("/\d{4}-\d{2}-\d{1}/", $item)) {
          continue;
        } else if (preg_match("/\d{4}-\d{2}/", $item)) {
          $date = date_create_from_format("Y-m", $item);
        } else if (preg_match("/\d{4}/", $item)) {
          $date = date_create_from_format("Y", $item);
        }

        if (!in_array($date->format("Y"), $array[$year])) {
          array_push($array[$year], $date->format("Y"));
        }
      } else {
        $letter = substr($item, 0, 1);

        if (!array_key_exists($letter, $array)) {
          $array[$letter] = array();
        }

        array_push($array[$letter], $item);
      }
    }

    return $array;
  }

  public function convertBrowse($results) {
    $array = array();

    foreach ($results as $key=>$result) {
      $explode = explode(";", $result[0]);

      foreach ($explode as $item) {
        $item = trim($item);

        if ($item === "") {
          continue;
        }

        $letter = substr($item, 0, 1);

        if (!array_key_exists($letter, $array)) {
          $array[$letter] = array($item);
        } else if (!in_array($item, $array[$letter])) {
          $array[$letter][] = $item;
        }
      }
    }

    ksort($array, SORT_STRING);

    foreach ($array as $key=>$value) {
      sort($array[$key], SORT_STRING);
    }

    return $array;
  }

  /**
   * Returns a URL for a Manuscript image.
   *
   * @param  String $pointer
   *   Pointer of the Manuscript.
   * @param  String $imageWidth
   *   Width of the image.
   * @param  String $imageHeight
   *   Height of the image.
   * @param  String $alias
   *   The CONTENTdm alias. Defaults to Ravenel.
   * @return String
   */
  public function getManuscriptImage($pointer, $imageWidth, $imageHeight, $alias = 'rav') {
    $pointer = trim($pointer);
    $imageWidth = trim($imageWidth);
    $imageHeight = trim($imageHeight);

    return "http://digital.tcl.sc.edu/utils/ajaxhelper/?CISOROOT=" . $alias . "&CISOPTR=" . $pointer . "&action=2&DMWIDTH=" . $imageWidth . "&DMHEIGHT=" . $imageHeight;
  }

  /**
   * Returns information about a manuscript compound object.
   *
   * @param  string $pointer
   *   Pointer of the Manuscript.
   * @param  string $alias
   *   The CONTENTdm alias. Defaults to Ravenel.
   * @return string
   */
  public function getManuscriptCompoundObjectInfo($pointer, $alias = 'rav') {
    $pointer = trim($pointer);

    return 'http://digital.tcl.sc.edu:81/dmwebservices/?q=dmGetCompoundObjectInfo/' . $alias . '/' . $pointer . '/json';
  }

  /**
   * Search Highlighter.
   *
   * Highlights a result with a given search term.
   *
   * @param  string $result
   *   The text that may be possibly highlighted.
   * @param  string $search
   *   The text to highlight with.
   * @return string
   *   The highlighted text (if possible).
   */
  public function highlightSearchTerm($result, $search) {
    if (gettype($result) === "array" && empty($result)) {
      $result = "";
    } else if (gettype($result) === "string") {
      $result = trim($result);
    }

    if (gettype($search) === "array" && empty($array)) {
      $search = "";
    } else if (gettype($search) === "string") {
      $search = trim($search);
    }

    // Remove the appended asterisk from the search when a user is coming from the
    // date column in browse.
    if (isset($_GET["browse"]) && $_GET["browse"] === "date") {
      $search = substr($search, 0, -1);
    }

    // Detect and internally remove a semicolon for highlighting.
    if (substr($search, strlen($search) - 1, strlen($search)) === ";") {
      $search = substr($search, 0, strlen($search) - 1);
    }

    if ($result === "" || $search === "") {
      return $result;
    } else if (count(explode(" ", $search)) !== 0 && !isset($_GET["browse"])) {
      $output = "";

      // Explode each search item by a space.
      foreach (explode(" ", $search) as $item) {
        if (stripos($result, $item) === false) { continue; }

        // If the print is still empty, then read the result, otherwise, read what we've already parsed.
        if (trim($output) === "") {
          $output = substr($result, 0, stripos($result, $item)) . '<span class="highlight">' . substr($result, stripos($result, $item), strlen($item)) . "</span>" . substr($result, stripos($result, $item) + strlen($item), strlen($result));
        } else {
          $output = substr($output, 0, stripos($output, $item)) . '<span class="highlight">' . substr($output, stripos($output, $item), strlen($item)) . "</span>" . substr($output, stripos($output, $item) + strlen($item), strlen($output));
        }
      }

      // In the instance that no items were highlighted, return the initial result.
      return trim($output) === "" ? $result : trim($output);
    } else if (gettype($result) === "string" && gettype($search) === "string" && stripos($result, $search) === false) {
      // This must return after the space explosion because most times, a search query with different words
      // separated by a space will return false in this instance.
      return $result;
    } else if (gettype($result) === "string" && gettype($search) === "string") {
      // Most times, this is returned when there is a one worded search query.
      return substr($result, 0, stripos($result, $search)) . '<span class="highlight">' . substr($result, stripos($result, $search), strlen($search)) . "</span>" . substr($result, stripos($result, $search) + strlen($search), strlen($result));
    }
  }

  /**
   * List Item Renderer.
   *
   * This function starts off determining if the passed in data is blank or not, if
   * it is, tell the user. If the passed in data is not blank, continue on, explode
   * the data, and render.
   *
   * @param  string $data
   *   The data returned from a database.
   * @param  string $header
   *   The data's heading.
   * @param  string $search
   *   The user's search query.
   * @return string
   */
  public function renderListItem($data, $header, $search) {
    $data = trim($data);

    // Return "Unknown or Not Applicable" when the data is empty.
    if ($data === "") {
      return '<li class="list-group-item"><h4 class="list-group-item-heading">' . $this->visualMetadata($header) . '</h4><p class="list-group-item-text"><em>Unknown or Not Applicable</em></p></li>';
    }

    // Create a paragraph for each item in one field.
    $paragraph = "";
    foreach (explode(";", $data) as $line) {
      $paragraph .= '<p class="list-group-item-text">' . $this->highlightSearchTerm($line, $search) . '</p>';
    }

    return '<li class="list-group-item"><h4 class="list-group-item-heading">' . $this->visualMetadata($header) . '</h4>' . $paragraph . '</li>';
  }

  /**
   * Metadata Converter.
   *
   * Converts server metadata values into an easier reading value.
   *
   * @param  string $metadata
   *   The server-sided category.
   * @return string
   */
  public function visualMetadata($metadata) {
    switch ($metadata) {
      case "decimalLongitude":
      case "decimalLatitude":
      case "lattit":
      case "latitu":
        return "Coordinates";

      case "stateProvince":
      case "locality":
      case "country":
      case "county":
      case "geogra":
        return "Location";

      case "identifiedBy":
        return "Identified By";

      case "recordedBy":
        return "Collected By";

      case "eventDate":
        return "Date";

      case "scientificName":
      case "scient":
        return "Scientific Plant Names";

      case "common":
        return "Common Plant Names";

      case "descri":
        return "Description";

      case "transc":
        return "Transcript";

      case "creato":
        return "People";

      case "subjec":
        return "Subject";

      case "publis":
        return "Contributing Institution";

      case "otherCatalogNumbers":
      case "pointer":
        return "Thumbnail";

      case "cultivationStatus":
        return "Cultivation Status";

      default:
        return ucfirst($metadata);
    }
  }

  /**
   * Random Manuscript
   *
   * Randomly returns a manuscript's pointer.
   *
   * @return String
   */
  public function randomManuscript() {
    $prepare = $this->connection->prepare("
      SELECT pointer
      FROM   manuscripts
    ");

    $prepare->execute();

    return $this->randomize($prepare->fetchAll(PDO::FETCH_COLUMN));
  }

  /**
   * Random Specimen
   *
   * Randomly returns a specimen's id.
   *
   * @return String
   */
  public function randomSpecimen() {
    $prepare = $this->connection->prepare("
      SELECT id
      FROM   plants
    ");

    $prepare->execute();

    return $this->randomize($prepare->fetchAll(PDO::FETCH_COLUMN));
  }

  /**
   * Randomizer
   *
   * Returns a random key within a given array.
   *
   * @param  Array   $array - Self-explanatory.
   * @return Integer
   */
  private function randomize($array) {
    return $array[rand(0, count($array))];
  }

  /**
   * Email Sender.
   *
   * Sends a piece of mail to the corresponding receiver.
   *
   * @param  string $name
   *   The name of the sender.
   * @param  string $email
   *   The sender's email.
   * @param  string $category
   *   The type of feedback being sent.
   * @param  string $text
   *   The sender's message.
   * @param  string $captcha
   *   The reCAPTCHA's result.
   * @param  string $url
   *   The URL the form is being submitted on.
   * @param  string $platform
   *   Basic information about the sender's browser.
   * @return JSON
   */
  public function sendMail($name, $email, $category, $text, $captcha, $url, $platform) {
    // Validate CAPTCHA.
    $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . CAPTCHA_SECRET_KEY . "&response=" . $captcha));
    if ($response->success != 1) {
      if ($response->{"error-codes"}[0] == "missing-input-response") {
        return json_encode(array("status" => "warning", "text" => "The CAPTCHA response is missing."));
      } else if ($response->{"error-codes"}[0] == "invalid-input-response") {
        return json_encode(array("status" => "warning", "text" => "The CAPTCHA response is invalid or malformed."));
      } else {
        return json_encode(array("status" => "danger", "text" => "Failure to validate CAPTCHA. Error Code {" . $response->{"error-codes"}[0] . "}. Please try again."));
      }
    }

    // Validate name.
    if ($name === "") {
      return json_encode(array("status" => "warning", "text" => "Please enter a name."));
    } else if (50 < strlen($name)) {
      return json_encode(array("status" => "warning", "text" => "Maximum name length is 50 characters."));
    }

    // Validate email.
    if ($email === "") {
      return json_encode(array("status" => "warning", "text" => "Please enter an email."));
    } else if (100 < strlen($email)) {
      return json_encode(array("status" => "warning", "text" => "Maximum email address length is 100 characters."));
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return json_encode(array("status" => "warning", "text" => "Please enter a valid email."));
    }

    // Validate message.
    if ($text === "") {
      return json_encode(array("status" => "warning", "text" => "Please enter a message."));
    } else if (300 < strlen($text)) {
      return json_encode(array("status" => "warning", "text" => "Maximum message length is 300 characters."));
    }

    // Determine sender.
    if ($category === "general") {
      $person   = "John";
      $receiver = "johnthomasknox@gmail.com";
    } else if ($category === "manuscripts") {
      $person   = "Kate";
      $receiver = "boydkf@mailbox.sc.edu";
    } else if ($category === "specimens") {
      $person   = "Herrick";
      $receiver = "brownh@biol.sc.edu";
    } else {
      return json_encode(array("status" => "warning", "text" => "Please select a feedback category."));
    }

    // Create headers.
    $headers  = "From: " . $name . " <" . $email . ">" . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8";

    // Create the message.
    $message  = "<html><body>";
    $message .= "<h4>Hello " . $person . ", </h4>";
    $message .= "<p>There has been a feedback submission on Plants and Planters: Henry William Ravenel website.</p>";
    $message .= "<p>Message:</p>";
    $message .= "<p>" . $text . "</p>";
    $message .= "<hr>";
    $message .= "<p>Other Content:</p>";
    $message .= "<p>Name: " . $name . "</p>";
    $message .= "<p>Email: " . $email . "</p>";
    $message .= "<p>Platform: " . $platform . "</p>";
    $message .= "<p>URL of Feedback: " . $url . "</p></body></html>";

    // Send the mail.
    if (mail($receiver, "Plants and Planter: Henry William Ravenel Feedback", $message, $headers)) {
      return json_encode(array("status" => "success", "text" => "Thank you " . $name . ", your feedback has been sent!"));
    } else {
      return json_encode(array("status" => "danger", "text" => "There was a failure trying to send the email. Please try again."));
    }
  }

  /**
   * DEBUGGING PURPOSES ONLY.
   *
   * Prints the contents of an array.
   *
   * @param  array $array
   *   The array to be printed.
   */
  public function printArray($array) {
    print '<pre style="white-space: pre;">' . print_r($array, true) . "</pre>";
  }

  /**
   * Accessors.
   */
  public function getConnection() {
    return $this->connection;
  }

  public function getTitle() {
    return $this->title;
  }

  /**
   * Mutators.
   */
  public function setTitle($title) {
    $this->title = $title;
  }
}
