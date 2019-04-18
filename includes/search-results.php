<?php
/**
 * @file includes/search-results.php
 * @author Collin Haines - Center for Digital Humanities
 */

require("configuration.php");

class SearchResults
{
	private $search;
	private $inputs;
	private $options;
	private $operators;

	private $clemsonQuery;
	private $carolinaQuery;

	private $letters;
	private $journals;
	private $specimens;

	/*
		Constructor
		Basic constructor that initializes class-wide variables.
	*/
	public function __construct()
	{
		$this->search = "";
		$this->inputs = array();
		$this->options = array();
		$this->operators = array("and");

		$this->clemsonQuery = "";
		$this->carolinaQuery = "";

		$this->letters = array();
		$this->journals = array();
		$this->specimens = array();
	}

	/*
		FUNCTION: createManuscriptQueries
		PARAMETERS
			counter - The n-th time this was executed.
			insituteToSkip - Used if we already have a living query from an institute.
		DESCRIPTION
			An algorithm to determine what needs to be parsed together to create a query for CONTENTdm and retrieve at least one result.
	*/
	public function createManuscriptQueries($counter=0, $instituteToSkip="") {
		$field = array();
		$input = array();

		if($this->search == "")
		{
			$input = $this->inputs;
			$field = $this->options;
		}
		else if(empty($this->inputs) && empty($this->options))
		{
			$explode = explode(" ", str_replace(" and ", " ", $this->search));

			if(!isset($_GET["browse"]) || (isset($_GET["browse"]) && count($explode) < 7))
			{
				$input = $explode;
			}
			else if(isset($_GET["browse"]))
			{
				$input = array_fill(0, 1, $this->search);
			}

			$field = array_fill(0, count($input), isset($_GET["browse"]) ? $_GET["browse"] : "CISOSEARCHALL");
		}

		$logic = $this->search == "" ? $this->operators : array_fill(0, count($input), "and");

		$center = "";
		for($i = 0; $i < count($input); $i++)
		{
			if($i === 6)
			{
				break;
			}

			if(0 < $i)
			{
				$center .= "^" . $logic[$i - 1] . "!";
			}

			if($counter >= 2 && $i == 0 && count($input) == 1) // There's one word and this is at least the second time this has been ran.
			{
				$center .= $field[$i] . "^*" . $input[$i] . "*^any";
			}
			else if($counter >= 2 && count($input) === ($i + 1))
			{
				// There needs to be an asterisk on the end of the last search term.
				$center .= $field[$i] . "^" . $input[$i] . "*^any";
			}
			else if ($counter >= 2 && $i == 0)
			{
				// There needs to be an asterisk on the start of the first search term.
				$center .= $field[$i] . "^*" . $input[$i] . "^any";
			}
			else
			{
				// There does not need to be an asterisk anywhere.
				$center .= $field[$i] . "^" . $input[$i] . "^any";
			}
		}

		if($instituteToSkip != "Clemson")
		{
			//TODO::CheckLink - also make these links not hard coded, give defines somewhere at the very least
			 $this->clemsonQuery = "http://culcdm.clemson.edu:81/dmwebservices/index.php?q=dmQuery/rvl/".str_replace("people^","creato^", str_replace("latitt^","latitu^", $center))."/common!creato!date!descri!geogra!latitu!scient!title!transc/0/1024/0/0/0/0/0/1/json";
	
			//$this->clemsonQuery = "http://digitalcollections.clemson.edu:81/dmwebservices/index.php?q=dmQuery/rvl/" . str_replace("people^", "creato^", str_replace("latitt^", "latitu^", $center)) . "/common!creato!date!descri!geogra!latitu!scient!title!transc/0/1024/0/0/0/0/0/1/json";
		}

		if($instituteToSkip != "Carolina")
		{
			$this->carolinaQuery = LINK_USC_S17."?q=dmQuery/rav/".$center."/common!date!descri!geogra!lattit!people!scient!title!transc/0/1024/0/0/0/0/0/1/json";
		}
	}

	/*
		FUNCTION: organizeManuscriptResults
		DESCRIPTION
			Determines if the initial query to each institution returns results. If not, try
			again for a maximum of three times. Once completed, prepare to split the data.
	*/
	public function organizeManuscriptResults() {
		$clemson = array();
		$carolina = array();

		$skipClemson = false;
		$skipCarolina = false;

		for($i = 1; $i < 4; $i++)
		{
			if(!$skipClemson)
			{
				$clemson = GetJSONDataFromLink($this->clemsonQuery,true);
			}

			if(!$skipCarolina)
			{
				$carolina = GetJSONDataFromLink($this->carolinaQuery,true);
			} 

			if(!isset($clemson["records"]) || empty($clemson["records"]))
			{
				$this->createManuscriptQueries($i,"Carolina");
			}
			else
			{
				$skipClemson = true;
			}

			if(!isset($carolina["records"]) || empty($carolina["records"]))
			{
				$this->createManuscriptQueries($i,"Clemson");
			}
			else
			{
				$skipCarolina = true;
			}

			if(isset($clemson["records"]) && !empty($clemson["records"]) && isset($carolina["records"]) && !empty($carolina["records"]))
			{ 
				break;
			}
		}

		if(isset($clemson["records"]))
		{
			$this->splitData($clemson["records"]);
		}
		if(isset($carolina["records"]))
		{
			$this->splitData($carolina["records"]);
		}
	} 

	/*
		FUNCTION: splitData
		PARAMETERS
			records - An array of records returned from CONTENTdm that is our search results.
		DESCRIPTION
			Determines if the initial query to each institution returns results. If not, try
			again for a maximum of three times. Once completed, prepare to split the data.
	*/
	private function splitData($records)
	{
		if(is_array($records) || is_object($records))
		{
			foreach($records as $record)
			{
				// Detect if it is an image. Skip if so.
				if(!isset($record["pointer"]) || 9088 < $record["pointer"] && $record["pointer"] < 9154)
				{
					continue;
				}

				// Remove unnecessary metadata returned from the search results.
				unset($record["collection"],$record["filetype"],$record["parentobject"],$record["find"]);

				// Adjust the pointer to have a link.
				$link = "institute=".($record["pointer"] < 3000 ? 'Clemson' : 'Carolina');
				$link .= '&number='.$record["pointer"].'&search='.urlencode(str_replace(" ", "-", ($this->search == "" ? implode(" ", $this->inputs) : $this->search)));

				//TODO::CheckLink
				//TODO:: it looks like this link has already been updated to clemson's new system, but double check that
				//TODO:: give defines for all of these hard external addresses
				//$image = $record["pointer"] < 3000 ? 'http://culcdm.clemson.edu/utils/getthumbnail/collection/rvl/id/' : 'https://server17173.contentdm.oclc.org/dmwebservices/?q=dmQuery/rav/';

				//TODO::Clemson this clemson link still needs updating
				$image = $record["pointer"] < 3000 ? 'http://digitalcollections.clemson.edu/utils/getthumbnail/collection/rvl/id/' : 'http://digital.tcl.sc.edu/utils/getthumbnail/collection/rav/id/';

				$record["pointer"] = '<a href="'.ROOT_FOLDER.'viewer.php?type=transcript&'.$link.'"><img src="'.$image.$record["pointer"].'" class="img-responsive" alt="'.$record["title"].'"></a>';

				// Clemson is different. Here at USC, we accept it.
				if(isset($record["creato"]))
				{
					$record["people"] = $record["creato"];
					unset($record["creato"]);
				}

				if(isset($record["latitu"]))
				{
					$record["lattit"] = $record["latitu"];
					unset($record["latitu"]);
				}

				// Render proper HTML.
				foreach($record as $key => $value)
				{
					if($key == "pointer" || $key == "title")
					{
						continue;
					}

					$record[$key] = $this->renderTableDataCell($value,$key);
				}

				// Move the record into its correct category.
				if($this->isLetter($record["title"]))
				{
					array_push($this->letters,$record);
				}
				else
				{
					array_push($this->journals,$record);
				}
			}
		}
	}

	/*
		FUNCTION: renderTableDataCell
		PARAMETERS
			data - The manuscript object's current data value.
			type - The manuscript object's current data type.
		DESCRIPTION
			Data Cell Interior Renderer.
	*/
	private function renderTableDataCell($data, $type)
	{
		global $application;

		if((is_array($data) && empty($data)) || (is_string($data) && trim($data) == ""))
		{
			$retVal = "<em>Unknown or N/A</em>";
		}
		else if($type == "transc")
		{
			$retVal = "<pre>".$application->highlightSearchTerm($data,$this->search == "" ? implode(" ",$this->inputs) : $this->search)."</pre>";
		}
		else
		{
			$list = "";
			foreach($type == "date" ? $application->convertDate($data) : explode(";",$data) as $item)
			{
				$list .= "<li>".$application->highlightSearchTerm($item, $this->search == "" ? implode(" ",$this->inputs) : $this->search)."</li>";
			}

			$retVal = '<ul class="list-unstyled">'.$list."</ul>";
		}

		return utf8_encode($retVal);
	}

	/*
		FUNCTION: populateSpecimenData
		DESCRIPTION
			Retrieves the search results from Symbiota, our specimen database. Black magic
			is used in the first part to assure that there are no mistakes in parameter
			populating. Once completed, parse the data and have it idle until it is ready to
			be sent back to the client.
	*/
	public function populateSpecimenData()
	{
		global $mysqli; // set in configuration

		$keys = array("eventDate", "identifiedBy", "locality", "county", "stateProvince", "country", "decimalLatitude", "decimalLongitude", "habitat", "recordedBy", "cultivationStatus");

		// Create the MySQL query.
		$liker = "omoccurrences.scientificName LIKE CONCAT('%', ?, '%')";
		$query = "images.thumbnailurl, omoccurrences.otherCatalogNumbers, omoccurrences.scientificName";

		foreach($keys as $key)
		{
			$liker .= " OR omoccurrences.".$key." LIKE CONCAT('%', ?, '%')";
			$query .= ", omoccurrences.".$key;
		}

		$database = "SELECT $query FROM images, omoccurrences WHERE omoccurrences.otherCatalogNumbers IS NOT NULL AND omoccurrences.collectionCode = 'HWR' AND images.occid = omoccurrences.occid AND ($liker)";

		// echo "<script>console.log( 'Debug Objects: " . $database . "' );</script>";

		$statement = $mysqli->prepare($database);

		// Declare the dynamic number of parameters to be binded.
		// NOTE: Add 1 for the "scientificName" in the $liker initialization.
		$names[] = implode("", array_fill(0, count($keys) + 1, "s"));
		$parameters = array_fill(0, count($keys) + 1, $this->search == "" ? implode(" ",$this->inputs) : $this->search);

		for($i = 0; $i < count($parameters); $i++)
		{
			$name = "bind".$i;
			${$name} = $mysqli->real_escape_string($parameters[$i]); // creates a var $bind0 and set it equal to $mysqli->real_...
			$names[] = &${$name}; // put the parameters into an array to be passed into the call_user_func_array()
		}

		call_user_func_array(array($statement,"bind_param"),$names);

		// Execute the query.
		$statement->execute();

		// Grab the dynamic number of results.
		$statement->store_result();

		// Store the result.
		$statement->bind_result($thumbnailurl, $otherCatalogNumbers, $scientificName, $eventDate, $identifiedBy, $locality, $county, $stateProvince, $country, $decimalLatitude, $decimalLongitude, $habitat, $recordedBy, $cultivationStatus);

		$search = urlencode(str_replace(" ","-",$this->search == "" ? implode(" ",$this->inputs) : $this->search));

		// Run through all data.
		while($statement->fetch())
		{
			$data = array(
				"thumbnailurl" => '<a href="'.ROOT_FOLDER.'viewer.php?type=specimen&institute=Carolina&number='.trim($otherCatalogNumbers).'&search='.$search.'"><img src="'.trim($thumbnailurl).'" class="img-responsive" alt="'.trim($scientificName).'"></a>',
				"scientificName" => trim($scientificName),
				"eventDate" => trim($eventDate),
				"identifiedBy" => trim($identifiedBy),
				"location" => substr(str_replace(", ,","",trim($locality).", ".trim($county).", ".trim($stateProvince).", ".trim($country).", "),0,-2),
				"coordinates" => trim($decimalLatitude) == "" ? "" : trim($decimalLatitude).", ".trim($decimalLongitude),
				"habitat" => trim($habitat),
				"recordedBy" => trim($recordedBy),
				"cultivationStatus" => trim($cultivationStatus)
			);

			// Render proper HTML.
			foreach($data as $key => $value)
			{
				if($key == "thumbnailurl")
				{
					continue;
				}

				$data[$key] = $this->renderTableDataCell($value,$key);
			}

			$this->specimens[] = $data;
		}

		$statement->close();
	}

	/*
		FUNCTION: scanImageEntries
		DESCRIPTION
			Runs through all known pointers that are images and matches the user search
			against the title of the item.
	*/
	public function scanImageEntries()
	{
		$array = array("images" => array());

		for($pointer = 9089; $pointer < 9150; $pointer++)
		{
			//TODO::CheckLink - also give define somewhere
			$info = GetJSONDataFromLink(LINK_USC_S17.LINK_USC_GET_ITEM_INFO.$pointer."/id/json",true);

			if(isset($info["title"]) && stripos($info["title"],$this->search) !== false)
			{
				array_push($array["images"],'<div class="col-xs-6"><a href="'.ROOT_FOLDER.'viewer.php?type=transcript&institute=Carolina&number='.$pointer.'&search='.$this->search.'"><img src="'.ROOT_FOLDER.'img/gallery-small/'.$pointer.'.jpg" class="img-responsive"><p class="text-center">View Image</p></a></div>');
			}
		}

		return $array;
	} 

	/*
		FUNCTION: isLetter
		PARAMETERS
			title - The title of the manuscript.
		DESCRIPTION
			Determines based on a pre-set list of keywords if the given title is a letter.
	*/
	private function isLetter($title)
	{
		$predeterminedHeaders = array("Card", "Certificate", "Circular", "Classification", "Deed", "Envelope", "Essay", "Letter", "List", "Manual", "Note", "Postcard", "Receipt", "Report", "Statement", "Testament");

		foreach($predeterminedHeaders as $keyword)
		{
			if(stripos($title,$keyword) !== false)
			{
				return true;
			}
		}

		return false;
	}

	//Accessors
	public function getLetters()
	{
		return $this->letters;
	}
	public function getJournals()
	{
		return $this->journals;
	}
	public function getSpecimens()
	{
		return $this->specimens;
	}
	public function getSearch()
	{
		return $this->search;
	}

	//Mutators
	public function setSearch($search)
	{
		$this->search = $search;
	}
	public function setInputs($inputs)
	{
		$this->inputs = $inputs;
	}
	public function setOptions($options)
	{
		$this->options = $options;
	}
	public function setOperators($operators) 
	{
		$this->operators = $operators;
	}
} // class SearchResults


$search = new SearchResults();

if(isset($_GET["search"]))
{
	$search->setSearch($_GET["search"]);
}
else if(isset($_GET["options"],$_GET["input"]))
{
	$search->setInputs($_GET["input"]);
	$search->setOptions($_GET["options"]);

	if(isset($_GET["operators"]))
	{
		$search->setOperators($_GET["operators"]);
	}
}
else
{
	header("Location: ../search.php");
	exit;
} 

if($_GET["type"] == "journals" || $_GET["type"] == "letters")
{
	$search->createManuscriptQueries();
	$search->organizeManuscriptResults();

	$data = $_GET["type"] == "letters" ? $search->getLetters() : $search->getJournals();

	$jsonEncoded = json_encode(array("recordsTotal" => count($data), "recordsFiltered" => count($data), "data" => $data));
}
else if($_GET["type"] == "specimens")
{
	$search->populateSpecimenData();

	$returnArr = array();

	$specimens = $search->getSpecimens();

	if(is_array($specimens))
	{
		$returnArr = array('recordsTotal' => count($specimens), 'recordsFiltered' => count($specimens), 'data' => $specimens);
	}
	else
	{
		$returnArr = array('recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array());
	}

	$jsonEncoded = json_encode($returnArr);
	
}
else if($_GET["type"] == "photographs")
{
	$jsonEncoded = json_encode($search->scanImageEntries());
}

if(!is_string($jsonEncoded))
{
	$jsonEncoded = json_encode(array('recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => array())); // give the defaults back
	LogManager::LogError("Error encoding json in search-results. Type : specimens. Search : ".$search->getSearch().". Error Code: ".json_last_error());
}

exit($jsonEncoded);