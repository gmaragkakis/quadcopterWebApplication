<?php
	//header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
	header("Content-Type: application/json; charset=UTF-8");
	// Only allow POST requests
	if (strtoupper($_SERVER['REQUEST_METHOD']) != 'GET') {
	throw new Exception('Only POST requests are allowed');
  }
  
  // Make sure Content-Type is application/json 
  $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
  if (stripos($content_type, 'application/json') === false) {
	throw new Exception('Content-Type must be application/json');
  }
  
  // Read the input stream
  $body = file_get_contents("php://input");
   
  // Decode the JSON object
  $object = json_decode($body, true);
  //echo $body;
  $DeviceId = $object["deviceid"];
  // Throw an exception if decoding failed
  /*if (!is_array($object)) {
	throw new Exception('Failed to decode JSON object');
  }*/
	$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query="SELECT * FROM drone_data WHERE timestamp = (SELECT MAX(timestamp) FROM drone_data WHERE DeviceId = '$DeviceId') ORDER BY timestamp;";
	$result = mysqli_query($link,$query);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$myObj->GPS = $row["GPS"];
			$myJSON = json_encode($myObj);
		  	echo $myJSON;
		  //echo $row["GPS"];
		}
	  } 
	mysqli_close($link);	  
?>