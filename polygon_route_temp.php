<?php
header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
if(!isset($_COOKIE[$wordpress_logged_in_f3849f9f6f5cc4a26f80209c5658a162])) {
		$user_login = $_COOKIE['wordpress_logged_in_8de153c5c34729b1259f7c633a7cc8d8'];
		$user_login = explode("|", $user_login, 2);
		$user_login = $user_login[0];
		// set a cookie for 1 year
		//setcookie('wpb_visit_time', $visit_time, time()+31556926);
		//echo($userid);
		$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
		mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
		$query="select ID from quadc_users where user_login = '$user_login'";
		$result = mysqli_query($link,$query);
			if ($result->num_rows > 0) {	//select logged user ID
				while($row = $result->fetch_assoc()) {
				$userid = $row["ID"];
			}
		}
		mysqli_close($link);
	}
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
echo $body;

$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query="INSERT INTO quadc_wpgmza_polygon(map_id, polydata)
		VALUES ('$userid','$body')";
	//echo("query: $query");
	mysqli_query($link,$query);
	mysqli_close($link);
// Display the object
//print_r($object);
//echo $object;
?>