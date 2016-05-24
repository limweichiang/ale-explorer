<?php
	include 'config.php';
	
	if(isset($_GET["floor_id"])){
		$requestedFloorId = $_GET["floor_id"];
	}
	
	// Page Variables
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	$displayFloorImgUrl = "";
	
	// Pull Floor Listing
	$curlFloorReq = curl_init($aleFloorApiUrl);
	$curlFloorOpt = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$aleUname:$alePasswd",
		CURLOPT_HTTPHEADER => array('Content-type: application/json'),
		CURLOPT_SSL_VERIFYPEER => false
	);
	
	curl_setopt_array($curlFloorReq, $curlFloorOpt);
	if(! $floorJsonStr = curl_exec($curlFloorReq)){
		trigger_error(curl_error($curlFloorReq)); 
	}
	curl_close($curlFloorReq);
	$floorObj = json_decode($floorJsonStr);


	// Search for requested floor based floor_id
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		
		if($floorMsg->floor_id == $requestedFloorId){
			$displayFloorImgUrl = $aleUrl.$floorMsg->floor_img_path;
		}
	}
	
	// Pull Map Listing
	$curlMapReq = curl_init($displayFloorImgUrl);
	$curlMapOpt = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$aleUname:$alePasswd",
		CURLOPT_SSL_VERIFYPEER => false
	);
	
	curl_setopt_array($curlMapReq, $curlMapOpt);
	if(! $mapStr = curl_exec($curlMapReq)){
		trigger_error(curl_error($curlMapReq)); 
	}
	curl_close($curlMapReq);
	
	echo $mapStr;

?>