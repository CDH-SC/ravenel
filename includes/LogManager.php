<?php
	class LogManager
	{
		//FUNCTION : LogError
		//PARAMETERS
		//	errorMessage - This is the message associated with what caused the error
		//	fromFunc - This tells what file/function the error originiated from
		//	sql - This is the sql that was trying to be used when the error occurred
		//DESCRIPTION
		//	This function will log an error message into a file of the format YYYY-MMM
		static function LogError($errorMessage, $fromFunc="", $sql="", $link="")
		{
			$today = new DateTime("now",new DateTimeZone('America/New_York')); // use this since this is my time zone

			//this gives some order to the logs, this way, the error log will not ultimately become one massive blob
			$txtFile = date_format($today,'Y-M');
			$filePath = '/san_mounted/Logs/Errors/'.$txtFile.'.txt';

			$errorLog = fopen($filePath, 'a') or die('Unable to open error log.');

			$text = "Message: ".$errorMessage . "\r\n";

			if($fromFunc != "")
			{
				$text .= "Function: ".$fromFunc."\r\n";
			}

			if($sql != "")//some functions just call other functions, these dont use sql
			{
				$text .= "SQL:\r\n";
				$text .= $sql."\r\n\r\n";
			}

			if($link != "")//some functions just call other functions, these dont use sql
			{
				$text .= "LINK:  $link\r\n";
			}

			$text .= "Time: ".date_format($today,'M-d-Y - H:i:s')."\r\n\r\n";
			$text .= "------------------------------------------------------\r\n\r\n";

			fwrite($errorLog, $text);
			fclose($errorLog);
		}

		static function LogTestString($message)
		{
			$today = new DateTime("now",new DateTimeZone('America/New_York')); // use this since this is my time zone

			$filePath = '/san_mounted/Logs/Tests/testlog.txt';

			$errorLog = fopen($filePath, 'a') or die('Unable to open test log.');

			$text = "TIME [".date_format($today,'M-d-Y - H:i:s')."]\r\n";
			$text .= "Message: ".$message."\r\n\r\n";
			$text .= "------------------------------------------------------\r\n\r\n";

			fwrite($errorLog, $text);
			fclose($errorLog);
		}
	}
?>