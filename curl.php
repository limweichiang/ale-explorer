<?php

include 'config.php';

// Use php-curl to poll for JSON data
function curl_get_json($url, $uname, $passwd){
	$jsonStr = curl_get_obj($url, $uname, $passwd);
	return json_decode($jsonStr);
}

// Use php-curl to poll for object
function curl_get_obj($url, $uname, $passwd){
	$curlOpt = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERPWD => "$uname:$passwd",
		CURLOPT_HTTPHEADER => array('Content-type: application/json'),
		CURLOPT_SSL_VERIFYPEER => $GLOBALS['verifySsl']
	);

	$curlRequest = curl_init($url);
	curl_setopt_array($curlRequest, $curlOpt);
	
	if(! $obj = curl_exec($curlRequest)){
		trigger_error(curl_error($curlRequest)); 
	}

	curl_close($curlRequest);

	return $obj;
}

?>