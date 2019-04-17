<?php
	/**
	 * @file   includes/configuration.php
	 * @author Collin Haines - Center for Digital Humanities
	 *
	 * Define definitions. Import importable files.
	 */

	// TODO figure out what's going on here
	if($_SERVER["HTTP_HOST"] === "tundra.csd.sc.edu")
	{
		header("Location: //ravenel.cdh.sc.edu" . str_replace("/ravenel", "", $_SERVER["REQUEST_URI"]));
		exit;
	}

	// Error Reporting.
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	ini_set("max_execution_time", 300);

	define("ROOT_FOLDER", "http://35.237.120.212/");
	define("CAPTCHA_SECRET_KEY", "6Ld2sw0TAAAAAEDlwEbGetmD82GWBSGaoK09c9b5");

	//define all of the domains/links used across the site
	define("LINK_RAVS_REPORT_COMMISSIONER", "http://biodiversitylibrary.org/page/29491027");

	define("LINK_USC_S17", "https://server17173.contentdm.oclc.org/dmwebservices/index.php");
	define("LINK_USC_GET_ITEM_INFO", "?q=dmGetItemInfo/rav/");
	define("LINK_USC_GET_IMAGE_INFO", "?q=dmGetImageInfo/rav/");
	define("LINK_USC_GET_COMPOUND_OBJ", "?q=dmGetCompoundObjectInfo/rav");

	define("LINK_USC_TCL_UTILS", "http://digital.tcl.sc.edu/utils/");
	define("LINK_USC_TCL_AJAXHELPER", "ajaxhelper/");
	define("LINK_USC_TCL_THUMBNAIL", "getthumbnail/collection/rav/id/");

	date_default_timezone_set("America/New_York");

	// Import.
	require_once("LogManager.php");
	require_once("application.php");
	require_once("manuscript.php");
	require_once("specimen.php");

	// Definitions.

	// TODO clean up this ROOT_FOLDER variable section
	
	//define("ROOT_FOLDER", "http://" . $_SERVER["SERVER_NAME"] . "/"); // TODO fix this stuff

	// Initialize.
	$application = new Application();

	//MySQL Connection.
	$mysqli = new mysqli("localhost", "CDHconnector", "Andromeda940", "floracaroliniana");

	if($mysqli->connect_error)
	{
		LogManager::LogError('Connect Error: ' . $mysqli->connect_error,"configuration.php");
		die('Connect Error: '.$mysqli->connect_error);
	}
	//$pgsql = pg_connect("host=107.170.104.93 port=5432 dbname=floracaroliniana user=CDHconnector password =!HWRPl@nt$")

	//if ($mysqli->connect_error) {
	//  exit("<h1>Database Connection Error (" . $mysqli->connect_errno . ") - " . $mysqli->connect_error . "</h1>");
	//} // if ($mysqli->connect_error)

	function GetJSONDataFromLink($link,$returnEmptyArrayOnError=false)
	{
		set_error_handler(
			function()
			{ 
				throw new Exception("Could not find path: ");
			},
			E_WARNING
		);

		$success = true;
		try
		{
			$result = json_decode(file_get_contents($link),true);
		}
		catch (Exception $e)
		{
			$success = false;
			$result = 'Caught exception: '.$e->getMessage().$link;
		}

		restore_error_handler();

		if($success)
		{
			return $result;
		}
		else
		{
			LogManager::LogError($result,"GetJSONDataFromLink()");
			if($returnEmptyArrayOnError)
			{
				return array();
			}
			else
			{
				return false;
			}
		}
	}

	function GetXMLDataFromLink($link,$returnEmptyArrayOnError=false)
	{
		set_error_handler(
			function()
			{ 
				throw new Exception("Could not find path: ");
			},
			E_WARNING
		);

		$doc = new DOMDocument();
		$success = true;

		try
		{
			$doc->load($link);
		}
		catch (Exception $e)
		{
			$success = false;
			$errorMessage = 'Caught exception: '.$e->getMessage().$link;
		}

		restore_error_handler();

		if($success)
		{
			return $doc;
		}
		else
		{
			LogManager::LogError($errorMessage,"GetXMLDataFromLink()");
			if($returnEmptyArrayOnError)
			{
				return array();
			}
			else
			{
				return false;
			}
		}
	}
?>