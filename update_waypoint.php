<?php
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
  //echo $body."\r\n";
   
  // Decode the JSON object
  $object = json_decode($body, true);
  $DeviceName = $object["device_names"];
  //echo "Device name from JSON: ".$DeviceName."\r\n";

  $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
  mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
  $query = "UPDATE RouteToExecute SET waypoint_reached = '1' WHERE device_name = '$DeviceName' AND waypoint_reached = '0' ORDER BY id ASC LIMIT 1";
  $result = mysqli_query($link,$query);

  mysqli_close($link);
  ?>