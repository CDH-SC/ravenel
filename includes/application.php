<?php
/**
 * @file   includes/application.php
 * @author Collin Haines - Center for Digital Humanities
 *
 * Backbone functionality for the website.
 */

define("JSON_CONTENTS", ROOT_FOLDER."includes/database.json");

class Application {
	private $json;
	private $title;

	/*
		Initialization Constructor.
	*/
	public function __construct()
	{
		$this->json = GetJSONDataFromLink(JSON_CONTENTS,true);
		$this->title = "";
	}

	/*
		FUNCTION: startsWith
		PARAMETERS
			haystack - The complete string.
			needle - The string to search for.
		DESCRIPTION
			This function returns a boolean value of wether or not the start of the haystack is equal to the search string.
	*/
	public function startsWith($haystack, $needle)
	{
		return substr($haystack, 0, strlen($needle)) === $needle;
	}

	/*
		FUNCTION: convertDate
		PARAMETERS
			dates - The date(s) to be converted.
		DESCRIPTION
			This function takes in an array of dates, and coverts them to a date in the format of 
			a full textual representation of the month, followed by a four digit representation of 
			the year.
	*/
	public function convertDate($dates)
	{
		$array = array();
		$dater = new DateTime();

		$explodedDates = explode(";",$dates);
		foreach ($explodedDates as $date)
		{
			$date = trim($date);

			// Sometimes, date fields consist of the year the manuscript was inserted into CONTENTdm, skip it.
			if (!isset($date) || $date == "" || $this->startsWith($date,"2") || preg_match("/\d{4}-\d{4}/",$date))
			{
				continue;
			}

			//check for yyyy-mm-dd format and it's digressions
			if(preg_match("/\d{4}-\d{2}-\d{2}/",$date))
			{
				array_push($array,$dater->createFromFormat("Y-m-d",$date)->format("F Y"));
			}
			else if(preg_match("/\d{4}-\d{2}/",$date))
			{
				array_push($array,$dater->createFromFormat("Y-m",$date)->format("F Y"));
			}
			else if(preg_match("/\d{4}/",$date))
			{
				array_push($array,$date);
			}
		}

		return array_unique($array);
	}

	//TODO::This currently just doesn't do what it's comments said it does, figure out how it actually flows
	/*
		FUNCTION: convertBrowseArray
		PARAMETERS
			items - The unordered array.
			key - The type of content.
		DESCRIPTION
			This function takes in an unordered array and returns an alphabetized array ready to render.
	*/

	//TODO:: fix this, I have broken this, look at the last part, the letter isn't like a note, it means like the character in the alphabet
	public function convertBrowseArray($items, $key)
	{
		$retArray = array();
		
		foreach($items as $item)
		{
			if(is_null($item) || $item === "")
			{
				continue;
			}

			if($key == "date")
			{
				//set up the date var
				if(preg_match("/\d{4}-\d{4}/",$item))
				{
					continue; // some entries have yyyy-yyyy as in a date range, we ignore these
				}
				if(preg_match("/\d{4}-\d{2}-\d{2}/",$item))
				{
					$date = date_create_from_format("Y-m-d",$item);
				}
				else if(preg_match("/\d{4}-\d{2}/",$item))
				{
					$date = date_create_from_format("Y-m",$item);
				}
				else if(preg_match("/\d{4}/", $item))
				{
					$date = date_create_from_format("Y",$item);
				}

				if(!is_a($date,'DateTime')) // sanity check
				{
					LogManager::LogError($item." is not compatible to converted to be a date");
					continue;
				}

				$decade = substr($item, 0, 3)."0s";
				if(!array_key_exists($decade, $retArray))
				{
					$retArray[$decade] = array();
				}

				//only add unique years
				if(!in_array($date->format("Y"),$retArray[$decade]))
				{
					array_push($retArray[$decade],$date->format("Y"));
				}
			}
			else
			{
				$letter = substr($item,0,1);

				if(!array_key_exists($letter,$retArray))
				{
					$retArray[$letter] = array();
				}

				array_push($retArray[$letter],$item);
			}
		}

		return $retArray;
	}

	/*
		FUNCTION: getManuscriptImage
		PARAMETER
			pointer - Pointer of the manuscript.
			imageWidth - The width of the image.
			imageHeight - The height of the image.
			alias - The CONTENTdm alias, this defaults to Ravenel.
		DESCRIPTION
			This function returns a URL for a Manuscript image.
	*/
	public function getManuscriptImage($pointer, $imageWidth, $imageHeight, $alias='rav')
	{
		$pointer = trim($pointer);
		$imageWidth = trim($imageWidth);
		$imageHeight = trim($imageHeight);
		//OMG! -> the digital.tcl.sc.edu still exists with this ajaxhelper!!!
		return LINK_USC_TCL_UTILS.LINK_USC_TCL_AJAXHELPER."?CISOROOT=$alias&CISOPTR=$pointer&action=2&DMWIDTH=$imageWidth&DMHEIGHT=$imageHeight";
	}

	/*
		FUNCTION: getManuscriptCompoundObjectInfo
		PARAMETER
			pointer - Pointer of the manuscript.
			alias - The CONTENTdm alias, this defaults to Ravenel.
		DESCRIPTION
			This function returns information about a manuscript compound object.
	*/
	public function getManuscriptCompoundObjectInfo($pointer, $alias='rav')
	{
		return LINK_USC_S17.LINK_USC_GET_COMPOUND_OBJ.$alias."/".trim($pointer)."/json"; //  <- this is the correct thing!!!
	}

	/*
		FUNCTION: highlightSearchTerm
		PARAMETERS
			result - The text that could be highlighted.
			search - Teh text to highlight with.
		DESCRIPTION
			This function highlights a result with a given search term.
	*/
	public function highlightSearchTerm($result, $search)
	{
		if(is_array($result) && empty($result))
		{
			$result = "";
		}
		else if(is_string($result))
		{
			$result = trim($result);
		}

		if(is_array($search) && empty($search))
		{
			$search = "";
		}
		else if(is_string($search))
		{
			$search = trim($search);
		}

		// Remove the appended asterisk from the search when a user is coming from the date column in browse.
		if(isset($_GET["browse"]) && $_GET["browse"] == "date" && strlen($search) > 0)
		{
			$search = substr($search, 0, -1);
		}

		//if the last char is a ';' remove it
		if(substr($search,strlen($search) - 1,strlen($search)) == ";")
		{
			$search = substr($search, 0, strlen($search) - 1);
		}

		$explodedSearch = explode(" ", $search);

		$output = "";

		if($result != "" && $search != "")
		{
			foreach($explodedSearch as $item)
			{
				//highlight each instance of the work that is in the result!
				while(stripos($result,$item) !== false)
				{
					//before item -> highlighted item
					$output .= substr($result, 0, stripos($result, $item));
					$output .= "<span class=\"highlight\">".$item."</span>";

					$result = substr($result,stripos($result, $item) + strlen($item)); // remove what we've added
				}
				
				$output .= $result; // this should be the rest of the string with no instances of item at this point
			}
		}
		else
		{
			$output = $result;
		}

		return $output;
	}

	/*
		FUNCTION: renderListItem
		PARAMETERS
			data - The data returned from a database.
			header - The data's heading.
			search - The user's search query.
		DESCRIPTION
			This function starts off determining if the passed in data is blank or not, if
			it is, tell the user. If the passed in data is not blank, continue on, explode
			the data, and render.
	*/
	public function renderListItem($data, $header, $search)
	{
		$data = trim($data);
		$paragraphs = "<p class=\"list-group-item-text\"><em>Unknown or Not Applicable</em></p>";

		// Create a paragraph for each item in one field.
		if($data !== "")
		{
			$paragraphs = "";
			$explodedData = explode(";", $data);
			foreach($explodedData as $line)
			{
				$paragraphs .= "<p class=\"list-group-item-text\">".$this->highlightSearchTerm($line,$search)."</p>";
			}
		}

		return "<li class=\"list-group-item\"><h4 class=\"list-group-item-heading\"".$this->visualMetadata($header)."</h4>".$paragraphs."</li>";
	}

	/*
		FUNCTION: visualMetadata
		PARAMETERS
			metadata - The server-side category.
		DESCRIPTION
			This function convers server metadata values into easier reading values.
	*/
	public function visualMetadata($metadata)
	{
		switch($metadata)
		{
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

	/*
		FUNCTION:
		PARAMETERS
			name - The name of the sender.
			email - The sender's email.
			category - The type of feedback being sent.
			text - The sender's message.
			captcha - The reCAPTCHA's result.
			url - The URL the form is being submitted on.
			platform - Basic information about the sender's browser.
		DESCRIPTION
			This function sends an email from the suggestion box at the bottom of the page.
	*/
	public function sendMail($name, $email, $category, $text, $captcha, $url, $platform)
	{
		// Validate CAPTCHA.
		$response = GetJSONDataFromLink("https://www.google.com/recaptcha/api/siteverify?secret=".CAPTCHA_SECRET_KEY."&response=$captcha");
		if($response != false)
		{
			if($response->success != 1)
			{
				if($response->{"error-codes"}[0] == "missing-input-response")
				{
					return json_encode(array("status" => "warning", "text" => "The CAPTCHA response is missing."));
				}
				else if($response->{"error-codes"}[0] == "invalid-input-response")
				{
					return json_encode(array("status" => "warning", "text" => "The CAPTCHA response is invalid or malformed."));
				}
				else
				{
					return json_encode(array("status" => "danger", "text" => "Failure to validate CAPTCHA. Error Code {" . $response->{"error-codes"}[0] . "}. Please try again."));
				}
			}

			// Validate name.
			if($name === "")
			{
				return json_encode(array("status" => "warning", "text" => "Please enter a name."));
			}
			else if(strlen($name) > 50)
			{
				return json_encode(array("status" => "warning", "text" => "Maximum name length is 50 characters."));
			}

			// Validate email.
			if($email === "")
			{
				return json_encode(array("status" => "warning", "text" => "Please enter an email."));
			}
			else if(strlen($email) > 100)
			{
				return json_encode(array("status" => "warning", "text" => "Maximum email address length is 100 characters."));
			}
			else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				return json_encode(array("status" => "warning", "text" => "Please enter a valid email."));
			}

			// Validate message.
			if($text === "")
			{
				return json_encode(array("status" => "warning", "text" => "Please enter a message."));
			}
			else if(strlen($text) > 300)
			{
				return json_encode(array("status" => "warning", "text" => "Maximum message length is 300 characters."));
			}
			//TODO:: check to see if 300 is the max that we want

			// Determine sender.
			if($category === "general")
			{
				$person   = "John";
				$receiver = "johnthomasknox@gmail.com";
			}
			else if($category === "manuscripts")
			{
				$person   = "Kate";
				$receiver = "boydkf@mailbox.sc.edu";
			}
			else if($category === "specimens")
			{
				$person   = "Herrick";
				$receiver = "brownh@biol.sc.edu";
			}
			else
			{
				return json_encode(array("status" => "warning", "text" => "Please select a feedback category."));
			}
			//TODO:: check if these are still the emails that we want

			// Create headers.
			$headers  = "From: $name <$email>\r\n";
			$headers .= "Reply-To: $email\r\n";
			$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=utf-8";

			// Create the message.
			$message  = "<html><body>";
			$message .= "<h4>Hello $person, </h4>";
			$message .= "<p>There has been a feedback submission on Plants and Planters: Henry William Ravenel website.</p>";
			$message .= "<p>Message:</p>";
			$message .= "<p>$text</p>";
			$message .= "<hr>";
			$message .= "<p>Other Content:</p>";
			$message .= "<p>Name: $name</p>";
			$message .= "<p>Email: $email</p>";
			$message .= "<p>Platform: $platform</p>";
			$message .= "<p>URL of Feedback: $url</p></body></html>";

			// Send the mail.
			if(mail($receiver, "Plants and Planter: Henry William Ravenel Feedback", $message, $headers))
			{
				return json_encode(array("status" => "success", "text" => "Thank you " . $name . ", your feedback has been sent!"));
			}
			else
			{
				return json_encode(array("status" => "danger", "text" => "There was a failure trying to send the email. Please try again."));
			}
		}
		else
		{
			LogManager::LogError("error from google link","sendMail");
			return json_encode(array("status" => "danger", "text" => "There was an error with the system."));
		}
	}

	/*
		FUNCTION: printArray
		PARAMETERS
			array - The array to be printed.
		DESCRIPTION
			This function prints the contents of an array in a formatted way.
	*/
	public function printArray($array)
	{
		print '<pre style="white-space: pre;">'.print_r($array, true)."</pre>";
	}

	/*
		Accessors.
	*/
	public function getJSON()
	{
		return $this->json;
	}

	public function getTitle()
	{
		return $this->title;
	}

	/**
		Mutators.
	*/
	public function setJSON($json)
	{
		$this->json = $json;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}
} // class Application
