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
    $query = "SELECT * from RouteToExecute WHERE device_name = '$DeviceName'";
    $result = mysqli_query($link,$query);
    $number_of_coordinates = mysqli_num_rows( $result );
    //echo "\r\nNumber of coordinates: ".$number_of_coordinates."\r\n";

    if($number_of_coordinates > 0){
        $query = "SELECT coordinates FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $next_waypoint = $row["coordinates"];
        if($next_waypoint == "")
        {
          echo "No more waypoints";
          $connectcmd = "/endroute";
        }
        //echo $next_waypoint."\r\n";
        $query = "SELECT altitude FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $altitude = $row["altitude"];
        //echo $altitude."\r\n";
        $query = "SELECT device_name FROM RouteToExecute WHERE id = (SELECT MIN(id)) AND waypoint_reached = '0' AND device_name = '$DeviceName'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $device_name = $row["device_name"];
        //echo $device_name."\r\n";
        
        $connectcmd = "/waypoint";
         
        $myObj->device_names = $device_name;
		    $myObj->connectcmd = '/waypoint';
		    $myObj->coordinates = $next_waypoint;
        $myObj->altitude = $altitude;
		    $postData = json_encode($myObj);
        header('Content-Type: application/json; charset=utf-8');
        echo $postData;
    }
    else if($number_of_coordinates == 0)
    {
      echo "There is no route for the device";
    }
    mysqli_close($link);
?>
        
        