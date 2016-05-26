<?php
	include 'config.php';
	include 'curl.php';
	
	// Page Variables
	$aleCampusApiUrl = "$aleUrl/api/v1/campus";
	$aleBuildingApiUrl = "$aleUrl/api/v1/building";
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	
	// Pull Campus listing
	$campusObj = curl_get_json($aleCampusApiUrl, $aleUname, $alePasswd);
	
	foreach($campusObj->Campus_result as $campusRes){
		$campusMsg = $campusRes->msg;
		$campusIdToNameMapping[$campusMsg->campus_id] = $campusMsg->campus_name;
	}

	// Pull Building listing
	$buildingObj = curl_get_json($aleBuildingApiUrl, $aleUname, $alePasswd);
	
	foreach($buildingObj->Building_result as $buildingRes){
		$buildingMsg = $buildingRes->msg;
		$buildingIdToNameMapping[$buildingMsg->building_id] = $buildingMsg->building_name;
		$campusToBuildingMapping[$buildingMsg->campus_id][] = $buildingMsg->building_id;
	}

	// Pull Floor listing
	$floorObj = curl_get_json($aleFloorApiUrl, $aleUname, $alePasswd);
	
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		$floorIdtoNameMapping[$floorMsg->floor_id] = $floorMsg->floor_name;
		$buildingToFloorMapping[$floorMsg->building_id][] = $floorMsg->floor_id;
	}
?>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="ale-explorer.css">
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
							echo '<li><a href="heatmap.php?floor_id='.$floorId.'">'.$floorIdtoNameMapping[$floorId].'</a></li>'."\n";
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