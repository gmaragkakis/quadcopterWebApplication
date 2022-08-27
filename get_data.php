<?php 
    //header("Content-Type: application/json; charset=UTF-8");
    header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
	//var_dump($_REQUEST);die;
	//$data = json_decode(file_get_contents("php://input"));
	// Takes raw data from the request
	//$json = file_get_contents('php://input');
	/*$_POST = json_decode(file_get_contents('php://input'), true);*/
	// Converts it into a PHP object
	// Only allow POST requests
	if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
  		throw new Exception('Only POST requests are allowed');
	}

	// Make sure Content-Type is application/json 
	$content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
	if (stripos($content_type, 'application/json') === false) {
  		throw new Exception('Content-Type must be application/json');
	}

	// Read the input stream
	$body = file_get_contents("php://input");
	echo($body);
	
 
	// Decode the JSON object
	$object = json_decode($body, true);

	// Throw an exception if decoding failed
	if (!is_array($object)) {
  	throw new Exception('Failed to decode JSON object');
	}
		//echo $object;
		//$data = $_POST;
		$data = $object;
		$GPS = $data["GPS"];
		$Yaw = $data["Yaw"];
		$Pitch = $data["Pitch"];
		$Roll = $data["Roll"];
		$DeviceId = $data["DeviceId"];
		$Altitude = $data["Altitude"];
		//mysqli_query($link,"SET NAMES 'utf8'");
    	//mysqli_query($link,"SET CHARACTER SET utf8");
    	//mysqli_query($link,"SET COLLATION_CONNECTION = 'utf8_general_ci'");
		$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
    	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
		
			$query="INSERT INTO drone_data(GPS, Yaw, Pitch, Roll, DeviceId, Altitude)
			VALUES ('$GPS','$Yaw','$Pitch','$Roll','$DeviceId','$Altitude')";
		//echo("query: $query");
		mysqli_query($link,$query);
		mysqli_close($link);
?>
