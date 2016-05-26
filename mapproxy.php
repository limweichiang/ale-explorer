<?php
	include 'config.php';
	include 'curl.php';
	
	if(isset($_GET["floor_id"])){
		$requestedFloorId = $_GET["floor_id"];
	}
	
	// Page Variables
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	$displayFloorImgUrl = "";
	
	// Pull Floor Listing
	$floorObj = curl_get_json($aleFloorApiUrl, $aleUname, $alePasswd);

	// Search for requested floor based floor_id
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		
		if($floorMsg->floor_id == $requestedFloorId){
			$displayFloorImgUrl = $aleUrl.$floorMsg->floor_img_path;
		}
	}
	
	// Pull Map Listing
	$mapStr = curl_get_obj($displayFloorImgUrl, $aleUname, $alePasswd);
	
	// Echo out floor plan image in binary.
	echo $mapStr;

?>