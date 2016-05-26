<?php
	include 'config.php';
	include 'curl.php';
	
	// Form Variables
	if(isset($_GET["floor_id"])){
		$requestedFloorId = $_GET["floor_id"];
	}
	
	// Page Variables
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	$aleLocationApiUrl = "$aleUrl/api/v1/location";
	$aleBuildingApiUrl = "$aleUrl/api/v1/building";
	$aleCampusApiUrl = "$aleUrl/api/v1/campus";
	
	// Display Variables
	$displayFloorImgUrl = "";
	$displayFloorImgWidth = 0;
	$displayFloorImgLength = 0;
	$displayFloorName = "";
	$displayBuildingName = "";
	$displayCampusName = "";
	$locationArray = array();
	$locationArrayRatioed = array();
	
	
	// Pull Campus listing
	$campusObj = curl_get_json($aleCampusApiUrl, $aleUname, $alePasswd);
	
	// Pull Building Listing
	$buildingObj = curl_get_json($aleBuildingApiUrl, $aleUname, $alePasswd);
	
	// Pull Floor Listing
	$floorObj = curl_get_json($aleFloorApiUrl, $aleUname, $alePasswd);
	
	// Pull Location Listing
	$locationObj = curl_get_json($aleLocationApiUrl, $aleUname, $alePasswd);
	
	// Search for requested floor based floor_id
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		
		if($floorMsg->floor_id == $requestedFloorId){
			$displayFloorName = $floorMsg->floor_name;
			$displayFloorImgUrl = $aleUrl.$floorMsg->floor_img_path;
			$displayFloorImgWidth = $floorMsg->floor_img_width;
			$displayFloorImgLength = $floorMsg->floor_img_length;
			$requestedBuildingId = $floorMsg->building_id;
		}
	}
	
	// Search for requested building based building_id
	foreach($buildingObj->Building_result as $buildingRes){
		$buildingMsg = $buildingRes->msg;
		
		if($buildingMsg->building_id == $requestedBuildingId){
			$displayBuildingName = $buildingMsg->building_name;
			$requestedCampusId = $buildingMsg->campus_id;
		}
	}
	
	// Search for requested campus based campus_id
	foreach($campusObj->Campus_result as $campusRes){
		$campusMsg = $campusRes->msg;
		
		if($campusMsg->campus_id == $requestedCampusId){
			$displayCampusName = $campusMsg->campus_name;
		}
	}
	
	// Search for and store all station coordinates for specified floor_id
	foreach($locationObj->Location_result as $locationRes){
		$locationMsg = $locationRes->msg;
		
		if($locationMsg->floor_id == $requestedFloorId){
			// Store raw coordinates for human readable requirements
			$staMac = $locationMsg->sta_eth_mac->addr;
			$staLocX = $locationMsg->sta_location_x;
			$staLocY = $locationMsg->sta_location_y;
			$locationArray[] = array($staMac, $staLocX, $staLocY);
			
			// Calculating and storing coordinates as a ratio over floor dimensions
			$staLocXRatioed = $staLocX/$displayFloorImgWidth;
			$staLocYRatioed = $staLocY/$displayFloorImgLength;
			$locationArrayRatioed[] = array($staMac, $staLocXRatioed, $staLocYRatioed);
		}
	}
?>

<html>
	<head>
		<script type="text/javascript" src="src/heatmap.js"></script>		
	</head>

	<body>

		<h1><?php echo $displayCampusName." - ".$displayBuildingName." - ".$displayFloorName; ?></h1>
		
		<div id="heatmap">
			<img id="floorplan" src="mapproxy.php?floor_id=<?php echo $requestedFloorId; ?>">
		</div>

		<table>
			<tr><td>Device MAC Address</td><td>x-Coordinate</td><td>y-Coordinate</td></tr>
			<?php
				foreach($locationArray as $location){
					$staMac = $location[0];
					$staLocX = $location[1];
					$staLocY = $location[2];			
					echo '<tr><td>'.$staMac.'</td><td>'.$staLocX.'</td><td>'.$staLocY.'</td></tr>'."\n";
				}		
			?>
		</table>
		
		<script type="text/javascript">
			window.onload = function(){
				var fpelement = document.getElementById('floorplan');
				var hmelement = document.getElementById('heatmap');

				// create configuration object
				var hmconfig = {
				  container: hmelement,
				  radius: 20,
				  maxOpacity: .4,
				  minOpacity: 0,
				  blur: .75
				};
				// create heatmap with configuration
				var heatmapInstance = h337.create(hmconfig);

				// Insert data points
				var max = 5;				
				var points = [		
				<?php
					$arrayLen = count($locationArrayRatioed);
					$arrayLenIter = 0;
					foreach($locationArrayRatioed as $locationRatioed){
						$arrayLenIter++;
						$xRatioed = $locationRatioed[1];
						$yRatioed = $locationRatioed[2];			
						echo "{x: Math.round($xRatioed".'*fpelement.width)'.", y: Math.round($yRatioed".'*fpelement.height)'.", value: 2}";
						if($arrayLenIter < $arrayLen){
							echo ", \n";
						}
					}
				?>
				];
				
				// heatmap data format
				var data = { 
				  max: max, 
				  data: points 
				};

				// if you have a set of datapoints always use setData instead of addData
				// for data initialization
				heatmapInstance.setData(data);
			};
		</script>
	</body>
</html>