<?php
	include 'config.php';
	
	// Form Variables
	if(isset($_GET["floor_id"])){
		$requestedFloorId = $_GET["floor_id"];
	}
	
	// Page Variables
	$aleFloorApiUrl = "$aleUrl/api/v1/floor";
	$aleLocationApiUrl = "$aleUrl/api/v1/location";
	
	// Display Variables
	$displayFloorImgUrl = "";
	$displayFloorImgWidth = 0;
	$displayFloorImgLength = 0;
	$displayFloorName = "";
	$locationArray = array();
	$locationArrayRatioed = array();
	
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
	
	// Pull Location Listing
	$curlLocationReq = curl_init($aleLocationApiUrl);
	$curlLocationOpt = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$aleUname:$alePasswd",
		CURLOPT_HTTPHEADER => array('Content-type: application/json'),
		CURLOPT_SSL_VERIFYPEER => false
	);
	
	curl_setopt_array($curlLocationReq, $curlLocationOpt);
	if(! $locationJsonStr = curl_exec($curlLocationReq)){
		trigger_error(curl_error($curlLocationReq)); 
	}
	curl_close($curlLocationReq);
	$locationObj = json_decode($locationJsonStr);
	
	// Search for requested floor based floor_id
	foreach($floorObj->Floor_result as $floorRes){
		$floorMsg = $floorRes->msg;
		
		if($floorMsg->floor_id == $requestedFloorId){
			$displayFloorName = $floorMsg->floor_name;
			$displayFloorImgUrl = $aleUrl.$floorMsg->floor_img_path;
			$displayFloorImgWidth = $floorMsg->floor_img_width;
			$displayFloorImgLength = $floorMsg->floor_img_length;
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

		<h1><?php echo $displayFloorName; ?></h1>
		
		<!-- Image is at <?php echo $displayFloorImgUrl; ?><br> -->
		
		<div id="heatmap">
			<img id="floorplan" src="<?php echo $displayFloorImgUrl; ?>">
		</div>

		<?php
			foreach($locationArray as $location){
				$staMac = $location[0];
				$staLocX = $location[1];
				$staLocY = $location[2];			
				echo "Device $staMac is at x: $staLocX, y: $staLocY".'<br>'."\n";
			}		
		?>
		
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
						echo "{x: Math.round($xRatioed".'*fpelement.width)'.", y: Math.round($yRatioed".'*fpelement.height)'.", value: 1}";
						if($arrayLenIter < $arrayLen){
							echo ", \n";
						}
					}
				?>
				];
				
				<!-- <?php echo 'console.log(fpelement.width);'; ?> -->
				<!-- <?php echo 'console.log(fpelement.height);'; ?> -->
				
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