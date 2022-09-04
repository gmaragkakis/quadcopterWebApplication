<?php
    require 'get_devices.php';
    //setcookie('device_names',$username,time()+60*60*24*365);
    //print_r($_COOKIE['device_names']);
    /*if(!isset($_COOKIE['device_names'])) {
        echo "Cookie named device_names is not set!";
      } else {
        echo "Cookie '" . device_names . "' is set!<br>";
        echo "Value is: " . $_COOKIE['device_names'];
      }*/
      //$_COOKIE['device_names'] = $username;
      //echo $username;
      //echo $_COOKIE['device_names'];
  /*header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
  if(!isset($_COOKIE[$device_names])) {
		$DeviceName = $_COOKIE['device_names'];
    echo "Cookie '" . device_names . "' is set!\r\n";
    echo "Value is: " . $_COOKIE['device_names']."\r\n";
  }*/
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
  echo $body."\r\n";
   
  // Decode the JSON object
  $object = json_decode($body, true);
  //$DeviceName = $object["device_names"];
  //echo "Device name from JSON: ".$DeviceName."\r\n";

    $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	  mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
    $query = "SELECT * from RouteToExecute";
    $result = mysqli_query($link,$query);
    $number_of_coordinates = mysqli_num_rows( $result );
    echo "\r\n".$number_of_coordinates."\r\n";

    if($number_of_coordinates > 0){
        $current_id_index = $number_of_coordinates - ($number_of_coordinates - 1);
        echo $current_id_index."\r\n";
        $query = "SELECT coordinates FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $next_waypoint = $row["coordinates"];
        echo $next_waypoint."\r\n";
        $query = "SELECT altitude FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $altitude = $row["altitude"];
        echo $altitude."\r\n";
        $query = "SELECT device_name FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $device_name = $row["device_name"];
        echo $device_name."\r\n";
        //$device_names;
        //$device_names = $_GET["device_names"];
        //echo "<script>document.writeln(p1);</script>";
        echo $device_names."\r\n";
		//echo '<script>document.getElementById("connect_button").getAttribute("src");</script>';
        $connectcmd = "/waypoint";
         
        $postData = array(
            'device_names'      => $DeviceName,
            'connectcmd'        => '/waypoint',
            'coordinates'       => $next_waypoint,
            'altitude'          => $altitude
          );

        $ch = curl_init('/get_device_id.php');
        curl_setopt_array($ch, array(CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_HTTPHEADER => array(
        //'Authorization: '.$authToken,
        'Content-Type: application/json'), CURLOPT_POSTFIELDS => json_encode($postData)));

        // Send the request
        $response = curl_exec($ch);

        // Check for errors
        if($response === FALSE){
            die(curl_error($ch));   
        }

        // Decode the response
        $responseData = json_decode($response, TRUE);

        // Close the cURL handler
        curl_close($ch);

        // Print the date from the response
        echo $responseData['published'];
    }
    mysqli_close($link);
?>
        
        