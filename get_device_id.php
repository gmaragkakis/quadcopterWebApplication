<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: "append,delete,entries,foreach,get,has,keys,set,values,Authorization"');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
	header("Content-Type: application/json; charset=UTF-8");
	/*header('Content-Type: text/html; charset=utf8mb4_unicode_ci');*/

	// Only allow POST requests
	if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
	throw new Exception('Only POST requests are allowed');
	}
  
  // Make sure Content-Type is application/json 
  $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
  if (stripos($content_type, 'application/json') === false) {
	throw new Exception('Content-Type must be application/json');
  }
  //
  // Read the input stream
  $body = file_get_contents("php://input");
   
  // Decode the JSON object
  $object = json_decode($body, true);
  
  $DeviceName = $object["device_names"];
  setcookie('connected_devices', $DeviceName, (time() + 31536000) , '/');
  $connectcmd = $object["connectcmd"];
  //echo $DeviceName;
  //echo $connectcmd;
	$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query = "SELECT * FROM drone_data JOIN drone_devices ON drone_devices.DeviceId = drone_data.DeviceId WHERE drone_devices.name = '$DeviceName' ORDER BY `timestamp` DESC LIMIT 1;";
	$result = mysqli_query($link,$query);
	$devices = [];
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$devices[] = $row;
		}
	  } 
	$devices = json_encode($devices);
	$query = "SELECT pagekiteURL FROM drone_devices WHERE drone_devices.name = '$DeviceName';";
	$result = mysqli_query($link,$query);
	$domainArray = array(); 
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$domainArray[] = $row;
		}
	  }
	$domain = $domainArray[0]['pagekiteURL'];
	if($connectcmd == "/connect"){
		$postRequest = "/connect";
	}
	else if($connectcmd == "/disconnect"){
		$postRequest = "/disconnect";
	}
	else if($connectcmd == "/waypoint"){
		$coordinates = $object["coordinates"];
		$altitude = $object["altitude"];
		//$postRequest = "/waypoint";
	}
	
	//Initialize cURL.
	$ch = curl_init();

	//Set the URL that you want to GET by using the CURLOPT_URL option.
	curl_setopt($ch, CURLOPT_URL, $domain);

	if($connectcmd == "/waypoint"){
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
		$myObj->waypoint = "/waypoint";
		$myObj->coordinates = $coordinates;
		$myObj->altitude = $altitude;
		$postRequest = json_encode($myObj);
		echo $postRequest;
	}
	
	//Set CURLOPT_POST to true, to execute HTTP POST request. 0 for GET.
	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);

	//Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	//Set CURLOPT_HEADER to true to get HTTP headers response.
	curl_setopt($ch, CURLOPT_HEADER, true);

	//Set CURLOPT_NOBODY to true for not receiving the body.
	//curl_setopt($ch, CURLOPT_NOBODY, true);

	//Execute the request.
	$response = curl_exec($ch);

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$headers = substr($response, 0, $header_size);
	$body = substr($response, $header_size);

	// Check HTTP status code
	if (!curl_errno($ch)) {
		switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
	  	case 200:
			echo $devices;
			break;
		//case 503:
	  	default:
			echo 'Unexpected HTTP code: ', $http_code, "\n";
			break;
		}
  	}

	//Close the cURL handle.
	curl_close($ch);

	header("Content-Type:text/plain; charset=UTF-8");
	
	//echo $body;
	
	//Print the data out onto the page.
	//echo $response;
	
	mysqli_close($link);	
	//echo $devices;  

?>