<?php
	require_once("configuration.php");
	//TODO::AddDefine for small gallery location
	$directory = scandir("/var/www/ravenel.com/img/gallery-small");

	$outStr = "";
	if(is_array($directory) && count($directory) > 2)
	{
		foreach($directory as $image)
		{
			if($image == "." || $image == "..")
			{
				continue;
			}
			
			$detail = GetJSONDataFromLink(LINK_USC_S17.LINK_USC_GET_ITEM_INFO.substr($image,0,-4)."/id/json",true);
			$title = (isset($detail["title"]) ? $detail["title"] : "");

			$outStr .= "<img src=\"".ROOT_FOLDER."img/gallery-small/$image\" class=\"img-responsive\" alt=\"$title\" data-image=\"".ROOT_FOLDER."img/gallery/$image\" data-description=\"$title\" style=\"display: none;\">";
		}
	}
	else
	{
		LogManager::LogError("No content found for/in /var/www/ravenel.com/img/gallery-small");
		$outStr .= "Error retrieving portraits, please try again later.";
	}

	exit($outStr);
?>