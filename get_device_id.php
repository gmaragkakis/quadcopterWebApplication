<?php
	include 'REST_API.php';
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
  //echo $DeviceName;
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
	
	//Initialize cURL.
	$ch = curl_init();

	//Set the URL that you want to GET by using the CURLOPT_URL option.
	curl_setopt($ch, CURLOPT_URL, $domain);

	//Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	//Set CURLOPT_HEADER to true to get HTTP headers response
	curl_setopt($ch, CURLOPT_HEADER, 1);

	//Execute the request.
	$data = curl_exec($ch);

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($data, 0, $header_size);
	$body = substr($data, $header_size);
	header("Content-Type:text/plain; charset=UTF-8");
	//echo $header;
	//echo $body;
	//Close the cURL handle.
	curl_close($ch);
	
	//Print the data out onto the page.
	//echo $data;
	
	mysqli_close($link);	
	echo $devices;  
?>