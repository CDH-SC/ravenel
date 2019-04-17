<?php
/**
 * @file includes/manuscript.php
 * @author Collin Haines - Center for Digital Humanities
 */

class Manuscript extends Exception
{
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
	 * The manuscript pointer.
	 * @param string $institute
	 * The institution the manuscript lives in.
	 */
	public function __construct($pointer, $institute)
	{
		if($institute != "Clemson" && $institute != "Carolina")
		{
			throw new Exception("The institute \"" . $institute . "\" is not supported. Please use either \"Clemson\" or \"Carolina\".");
		}

		// Determine if it's Clemson. If not, assume it's USC.
		$this->isClemson = $institute == "Clemson";
		//echo $this->isClemson;
		
		//Assign the CONTENTdm collection alias and domain.
		$this->alias = $this->isClemson ? "rvl" : "rav";
		$this->domain = $this->isClemson ? "culcdm.clemson" : ".tcl.sc"; //TODO::CheckLink
		//$this->domain = $this->isClemson ? "collections.clemson" : ".tcl.sc";

		// Get information.
		$this->data = $this->getJSONQuery("dmGetItemInfo",$pointer);
		$this->compound = $this->getJSONQuery("dmGetCompoundObjectInfo",$pointer);

		// Skip if it does not exist.
		if(array_key_exists("code",$this->data))
		{
			return;
		}

		// Grab the image. If it's a compound, grab the first image associated object.
		if(array_key_exists("page",$this->compound))
		{
			if(array_key_exists("0",$this->compound["page"]))
			{
				$location = $this->compound["page"][0]["pageptr"];
			}
			else
			{
				$location = $this->compound["page"]["pageptr"];
			}
		}
		else
		{
			$location = $pointer;
		}
		
		if($this->isClemson)
		{
			$this->thumb = "http://culcdm.clemson.edu/utils/getthumbnail/collection/".$this->alias."/id/".$location;
			$this->image = "http://culcdm.clemson.edu/utils/ajaxhelper/?CISOROOT=".$this->alias."&CISOPTR=".$location."&action=2";
		}
		else
		{
			$this->thumb = "http://digital.tcl.sc.edu/utils/getthumbnail/collection/".$this->alias."/id/".$location;
			$this->image = "http://digital.tcl.sc.edu/utils/ajaxhelper/?CISOROOT=".$this->alias."&CISOPTR=".$location."&action=2";
		}

		if($this->isClemson)
		{ 
			//$doc->load("http://culcdm.clemson.edu:81/dmwebservices/?q=dmGetImageInfo/".$this->alias."/".$pointer."/xml");
			$doc = GetXMLDataFromLink("http://culcdm.clemson.edu:81/dmwebservices/?q=dmGetImageInfo/".$this->alias."/".$pointer."/xml");
		}
		else
		{
			//$doc->load("http://server17173.contentdm.oclc.org/dmwebservices/?q=dmGetImageInfo/".$this->alias."/".$pointer."/xml");
			$doc = GetXMLDataFromLink("http://server17173.contentdm.oclc.org/dmwebservices/?q=dmGetImageInfo/".$this->alias."/".$pointer."/xml");
		}

		if($doc != false)
		{
			$this->image .= "&DMWIDTH=".$doc->getElementsByTagName("width")->item(0)->nodeValue."&DMHEIGHT=".$doc->getElementsByTagName("height")->item(0)->nodeValue;
		}
		// Initialize the rest of the variables.
		$this->search = "";
		$this->pointer = $pointer;
		$this->institute = $institute;
	} 

	/*
		FUNCTION: renderViewerMetadata
		DESCRIPTION
			Renders the HTML for the metadata section.
	*/
	public function renderViewerMetadata()
	{
		global $application;

		// Determine keys based on institute.
		if($this->isClemson)
		{
			$keys = array("title", "date", "subjec", "descri", "transc", "creato", "geogra", "scient", "common", "publis");
		}
		else
		{
			$keys = array("title", "date", "subjec", "descri", "people", "geogra", "scient", "common", "publis");
		}

		$metadata = "";

		foreach($keys as $key)
		{
			// Override $application->renderListItem() for side-by-side view.
			if($key == "scient")
			{
				global $mysqli;

				$metadata .= '<li class="list-group-item"><h4 class="list-group-item-heading">'.$application->visualMetadata($key).'</h4>';

				foreach(explode(";", $this->getData($key)) as $data)
				{
					if(trim($data) == "")
					{
						$metadata .= '<p class="list-group-item-text"><em>Unknown or Not Applicable</em></p>';
						break;
					}

					// Sanitize.
					$data = $mysqli->real_escape_string($data);

					$statement = $mysqli->prepare("SELECT omoccurrences.otherCatalogNumbers FROM images, omoccurrences WHERE scientificName = ? AND omoccurrences.otherCatalogNumbers IS NOT NULL AND omoccurrences.collectionCode = 'HWR' AND images.occid = omoccurrences.occid AND images.url IS NOT NULL LIMIT 1");
					$statement->bind_param("s", $data);
					$statement->execute();
					$statement->store_result();

					if($statement->num_rows == 0)
					{
						$metadata .= '<p class="list-group-item-text">' . $application->highlightSearchTerm($data, $this->search) . '</p>';
					}
					else
					{
						$statement->bind_result($catalog);
						$statement->fetch();

						$metadata .= '<p class="list-group-item-text viewer-specimen" data-catalog="'.$catalog.'">'.$application->highlightSearchTerm($data, $this->search).'</p>';
					}
				}

				$metadata .= '</li>';
			}
			else
			{
				$metadata .= $application->renderListItem($this->getData($key), $key, $this->search);
			}
		}

		return '<ul class="list-group">'.$metadata.'</ul>';
	}

	/*
		FUNCTION: retrieveSiblings
		DESCRIPTION
			Algorithm to determine if the current item has other items related to it.
	*/
	public function retrieveSiblings()
	{
		$parent = $this->getJSONQuery("GetParent",$this->pointer);

		if($parent["parent"] != "-1")
		{
			$this->compound = $this->getJSONQuery("dmGetCompoundObjectInfo",$parent["parent"]);
		}

		if(array_key_exists("page",$this->compound))
		{
			if(array_key_exists("0",$this->compound["page"]))
			{
				$total = count($this->compound["page"]) - 1;
				$place = $this->findPointerLocation($this->pointer,$this->compound);

				if($place === 0)
				{
					return $this->renderSiblingLink($this->compound["page"][$place + 1]["pageptr"],"right");
				}
				else if(0 < $place && $place < $total)
				{
					return $this->renderSiblingLink($this->compound["page"][$place - 1]["pageptr"],"left").$this->renderSiblingLink($this->compound["page"][$place + 1]["pageptr"],"right");
				}
				else if($place == $total)
				{
					return $this->renderSiblingLink($this->compound["page"][$place - 1]["pageptr"],"left");
				}
			}
			else if($parent["parent"] != "-1")
			{
				return $this->renderSiblingLink($parent["parent"],"right");
			}
			else
			{
				return $this->renderSiblingLink($this->compound["page"]["pageptr"],"left");
			}
		}
	}

	/*
		FUNCTION: findPointerLocation
		PARAMETERS
			pointer - The pointer to look for.
			compound - The array to search.
		DESCRIPTION
			Finds the index of a pointer in a compound object info array.
	*/
	private function findPointerLocation($pointer, $compound) {
		$count = 0;

		foreach($compound["page"] as $k=>$array)
		{
			if($array["pageptr"] == $pointer)
			{
				return $count;
			}

			$count++;
		}

		return -1;
	}

	/*
		FUNCTION: renderSiblingLink
		PARAMETERS
			pointer - The Manuscript pointer of the sibling.
			direction - Either left (Previous) or right (Next) direction.
		DESCRIPTION
			Renders the HTML for a link to the current item's sibling.
	*/
	private function renderSiblingLink($pointer, $direction)
	{
		$text = ($direction == "left" ? "Previous" : "Next");

		return '<a href="' . ROOT_FOLDER . 'viewer.php?type=transcript&institute='.$this->institute.'&number='.$pointer.'&" class="pull-'.$direction.' text-muted">'.$text.' Page</a>';
	}

	/*
		FUNCTION: getJSONQuery
		PARAMETERS
			query - The specific query to ask CONTENTdm.
			pointer - The item to look at.
		DESCRIPTION
			Converts a JSON array from CONTENTdm to an array to be used in this class.
	*/
	private function getJSONQuery($query, $pointer)
	{
		if($this->isClemson)
		{
			return GetJSONDataFromLink("http://culcdm.clemson.edu:81/dmwebservices/?q=".$query."/".$this->alias."/".$pointer."/json",true);
		}
		else
		{
			return GetJSONDataFromLink("http://server17173.contentdm.oclc.org/dmwebservices/?q=".$query."/".$this->alias."/".$pointer."/json",true);
		}
	}

	/*
		FUNCTION: getData
		PARAMETERS
			key - The metadata field key.
		DESCRIPTION
			Data Field Accessor.
	*/
	public function getData($key)
	{
		if($key == "people" && $this->isClemson)
		{
			$key = "creato";
		}
		else if($key == "creato" && !$this->isClemson)
		{
			$key = "people";
		}

		if(array_key_exists($key,$this->data) && is_string($this->data[$key]))
		{
			return empty($this->data[$key]) ? "" : trim($this->data[$key]); //TODO:: can't this just be returning trim with out the one liner
		}

		return "";
	}

	/*
		Accessors.
	*/
	public function getImage()
	{
		return $this->image;
	}

	public function getThumb()
	{
		return $this->thumb;
	}

	public function getPointer()
	{
		return $this->pointer;
	}

	public function getInstitute()
	{
		return $this->institute;
	}

	/*
		Mutators.
	*/
	public function setData($data)
	{
		$this->data = $data;
	}

	public function setImage($image)
	{
		$this->image = $image;
	}

	public function setThumb($thumb)
	{
		$this->thumb = $thumb;
	}

	public function setSearch($search)
	{
		$this->search = $search;
	}

	public function setPointer($pointer)
	{
		$this->pointer = $pointer;
	}

	public function setInstitute($institute)
	{
		$this->institute = $institute;
	}
} // class Manuscript
