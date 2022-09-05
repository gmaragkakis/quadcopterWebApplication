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

  // Throw an exception if decoding failed
if (!is_array($object)) {
    throw new Exception('Failed to decode JSON object');
  }
  //echo $body;

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
    $coordinate = substr($key["lat"],0,10) . "," . substr($key["lng"],0,10);
    //echo substr($key["lat"],0,10) . ", " . substr($key["lng"],0,10) . " ";
    $query = "INSERT INTO RouteToExecute(coordinates,altitude,waypoint_reached,device_name)
    VALUES ('$coordinate','$Altitude','0','$DeviceName')";
    $result = mysqli_query($link,$query);
    //echo $result; 
  }
  mysqli_close($link);

  // ------------------------------------------------------
  /* Send first waypoint to drone (via get_device_id.php) */
  // ------------------------------------------------------
  $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	  mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
    $query = "SELECT * from RouteToExecute";
    $result = mysqli_query($link,$query);
    $number_of_coordinates = mysqli_num_rows( $result );
    echo "\r\nNumber of coors: ".$number_of_coordinates."\r\n";

    //if($number_of_coordinates > 0){
        $current_id_index = $number_of_coordinates - ($number_of_coordinates - 1);
        echo "Current coord: ".$current_id_index."\r\n";
        $query = "SELECT coordinates FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $next_waypoint = $row["coordinates"];
        echo "Next waypoint is: ".$next_waypoint."\r\n";
        $query = "SELECT altitude FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $altitude = $row["altitude"];
        echo "Altitude: ".$altitude."\r\n";
        $query = "SELECT device_name FROM RouteToExecute WHERE id = '$current_id_index' AND waypoint_reached = '0'";
        $result = mysqli_query($link,$query);
        $row = $result->fetch_assoc();
        $device_name = $row["device_name"];
        echo "Device name: ".$device_name."\r\n";
        //$device_names;
        //$device_names = $_GET["device_names"];
        //echo "<script>document.writeln(p1);</script>";
        //echo $device_names."\r\n";
		    //echo '<script>document.getElementById("connect_button").getAttribute("src");</script>';
        $connectcmd = "/waypoint";
         

        $myObj->device_names = $device_name;
		    $myObj->connectcmd = '/waypoint';
		    $myObj->coordinates = $next_waypoint;
        $myObj->altitude = $altitude;
		    $postData = json_encode($myObj);
        echo 'Post Data: '.$postData;

        //Set CURLOPT_POST to true, to execute HTTP POST request. 0 for GET.
	      curl_setopt($ch, CURLOPT_POST, true);

	      curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

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
			      echo 'Status 200';
			      break;
		      //case 503:
	  	      default:
			      echo 'Unexpected HTTP code: ', $http_code, "\n";
            echo curl_errno();
			      break;
		  }
  	}

	  //Close the cURL handle.
	  curl_close($ch);

	  header("Content-Type:text/plain; charset=UTF-8");
	
        /*$postData = array(
            'device_names'      => $device_name,
            'connectcmd'        => '/waypoint',
            'coordinates'       => $next_waypoint,
            'altitude'          => $altitude
          );*/
        
        /*$ch = curl_init('/get_device_id.php');
        curl_setopt_array($ch, array(CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_HTTPHEADER => array(
        //'Authorization: '.$authToken,
        'Content-Type: application/json'), CURLOPT_POSTFIELDS => $postData));
        
        // Send the request
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
      
        // Check HTTP status code
        if (!curl_errno($ch)) {
          switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            case 200:
            echo 'HTTP code: ', $http_code, "\n";
            break;
          //case 503:
            default:
            echo 'Unexpected HTTP code: ', $http_code, "\n";
            break;
          }
        }*/
        // Check for errors
        //if($response === FALSE){
            //die(curl_error($ch));   
        //}

        // Decode the response
        //$responseData = json_decode($response, TRUE);

        // Close the cURL handler
        //curl_close($ch);

        // Print the date from the response
        //echo $responseData['published'];
        //$number_of_coordinates--;
    //}
    mysqli_close($link);
  /*header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
  if(!isset($_COOKIE[$wordpress_logged_in_f3849f9f6f5cc4a26f80209c5658a162])) {
		$DeviceName = $_COOKIE['connected_devices'];
    //$DeviceName = explode("|", $DeviceName, 2);
		//$DeviceName = $DeviceName[0];
    //echo "Cookie '" . device_names . "' is set!\r\n";
    echo "Name of device is: " . $DeviceName."\r\n";
    //echo "Name of device is: " . $DeviceName."\r\n";
  }*/
  //include 'waypoint_reached.php';

  /*$postData = array(
    'device_names'      => $DeviceName
  );

$ch = curl_init('http://www.quadcopter.gr/waypoint_reached.php');
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
echo $responseData['published'];*/
?>