<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: "append,delete,entries,foreach,get,has,keys,set,values,Authorization"');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

header("Content-Type: application/json; charset=UTF-8");

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
 
// Decode the JSON object
$object = json_decode($body, true);

// Throw an exception if decoding failed
if (!is_array($object)) {
    throw new Exception('Failed to decode JSON object');
}

  print_r($object);

  // ------------------------------------------------------
  /* Create RouteToExecute table in SQL and save data to */
  // ------------------------------------------------------
  $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query="CREATE TABLE RouteToExecute(
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coordinates VARCHAR(50) NOT NULL,
    altitude VARCHAR(50) NOT NULL,
    waypoint_reached TINYINT(1),
    device_name VARCHAR(50) NOT NULL
    )";
  $result = mysqli_query($link,$query);
  $RouteData = $object["RouteData"];
  $Altitude = $object["Altitude"];
  $DeviceName = $object["DeviceName"];
  //echo $RouteData;
  //echo $result;
  foreach ($RouteData as $key) {
    $coordinate = substr($key["lat"],0,10) . ", " . substr($key["lng"],0,10);
    //echo substr($key["lat"],0,10) . ", " . substr($key["lng"],0,10) . " ";
    $query = "INSERT INTO RouteToExecute(coordinates,altitude,waypoint_reached,device_name)
    VALUES ('$coordinate','$Altitude','0','$DeviceName')";
    $result = mysqli_query($link,$query);
    //echo $result; 
  }
  mysqli_close($link);

  // ------------------------------------------------------
  /* Send first waypoint to drone (via wp-coder js) */
  // ------------------------------------------------------
  $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	  mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
    $query = "SELECT * from RouteToExecute";
    $result = mysqli_query($link,$query);
    $number_of_coordinates = mysqli_num_rows( $result );
    echo "\r\nNumber of coors: ".$number_of_coordinates."\r\n";

    $current_id_index = $number_of_coordinates - ($number_of_coordinates - 1);
    echo "Current coord: ".$current_id_index."\r\n";
    "SELECT coordinates FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
    $result = mysqli_query($link,$query);
    $row = $result->fetch_assoc();
    $next_waypoint = $row["coordinates"];
    echo "Next waypoint is: ".$next_waypoint."\r\n";
    $query = "SELECT altitude FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
    $result = mysqli_query($link,$query);
    $row = $result->fetch_assoc();
    $altitude = $row["altitude"];
    echo "Altitude: ".$altitude."\r\n";
    $query = "SELECT device_name FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
    $result = mysqli_query($link,$query);
    $row = $result->fetch_assoc();
    $device_name = $row["device_name"];
    echo "Device name: ".$device_name."\r\n";
      
    $connectcmd = "/waypoint";   

    $myObj->device_names = $device_name;
		$myObj->connectcmd = '/waypoint';
		$myObj->coordinates = $next_waypoint;
    $myObj->altitude = $altitude;
		$postData = json_encode($myObj);
    echo 'Post Data: '.$postData;
	
    mysqli_close($link);
?>