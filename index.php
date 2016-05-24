<?php
	include 'config.php';
	
	// Page Variables
	$aleCampusApiUrl = "$aleUrl/api/v1/campus";
	$aleBuildingApiUrl = "$aleUrl/api/v1/building";
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	
	// Set Curl options
	$curlOpt = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$aleUname:$alePasswd",
		CURLOPT_HTTPHEADER => array('Content-type: application/json'),
		CURLOPT_SSL_VERIFYPEER => false
	);
	
	// Pull Campus listing
	$curlCampusReq = curl_init($aleCampusApiUrl);

	curl_setopt_array($curlCampusReq, $curlOpt);
	if(! $campusJsonStr = curl_exec($curlCampusReq)){
		trigger_error(curl_error($curlCampusReq)); 
	}
	curl_close($curlCampusReq);
	$campusObj = json_decode($campusJsonStr);
	
	foreach($campusObj->Campus_result as $campusRes){
		$campusMsg = $campusRes->msg;
		$campusIdToNameMapping[$campusMsg->campus_id] = $campusMsg->campus_name;
	}

	// Pull Building listing
	$curlBuildingReq = curl_init($aleBuildingApiUrl);

	curl_setopt_array($curlBuildingReq, $curlOpt);
	if(! $buildingJsonStr = curl_exec($curlBuildingReq)){
		trigger_error(curl_error($curlBuildingReq)); 
	}
	curl_close($curlBuildingReq);
	$buildingObj = json_decode($buildingJsonStr);
	
	foreach($buildingObj->Building_result as $buildingRes){
		$buildingMsg = $buildingRes->msg;
		$buildingIdToNameMapping[$buildingMsg->building_id] = $buildingMsg->building_name;
		//$buildingToCampusMapping[$buildingMsg->building_id][] = $buildingMsg->campus_id;
		$campusToBuildingMapping[$buildingMsg->campus_id][] = $buildingMsg->building_id;
	}

	// Pull Floor listing
	$curlFloorReq = curl_init($aleFloorApiUrl);

	curl_setopt_array($curlFloorReq, $curlOpt);
	if(! $floorJsonStr = curl_exec($curlFloorReq)){
		trigger_error(curl_error($curlFloorReq)); 
	}
	curl_close($curlFloorReq);
	$floorObj = json_decode($floorJsonStr);
	
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		$floorIdtoNameMapping[$floorMsg->floor_id] = $floorMsg->floor_name;
		//$floorToBuildingMapping[$floorMsg->floor_id][] = $floorMsg->building_id;
		$buildingToFloorMapping[$floorMsg->building_id][] = $floorMsg->floor_id;
	}
?>

<html>
	<head>	
	</head>

	<body>

		<h1>Floor Index</h1>
		
		<div id="floor-list">
			<ul id="campus-list">
			<?php
				foreach($campusIdToNameMapping as $campusId => $campusName){
					echo '<li>'.$campusName.'</li><ul id="building-list">'."\n";
					
					foreach($campusToBuildingMapping[$campusId] as $buildingId){
						echo '<li>'.$buildingIdToNameMapping[$buildingId].'</li><ul id="floor-list">'."\n";
						
						foreach($buildingToFloorMapping[$buildingId] as $floorId){
							echo '<li><a href="heatmap.php?floor_id='.$floorId.'">'.$floorIdtoNameMapping[$floorId].' - '.$floorId.'</a></li>'."\n";
						}
						echo '</ul>';
					}
					echo '</ul>';
				}
			?>
			</ul>
		</div>
	</body>
</html>
