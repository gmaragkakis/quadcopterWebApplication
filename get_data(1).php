<?php 
    header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
	var_dump($_REQUEST);die;
    $link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die("cannot connect"); //DB, user, pass
	$data = json_decode(file_get_contents("php://input"));
	$GPS = $data["GPS"];
	$Yaw = $data["Yaw"];
	$Pitch = $data["Pitch"];
	$Roll = $data["Roll"];
	$DeviceId = $data["DeviceId"];
	
	//mysqli_query($link,"SET NAMES 'utf8'");
    //mysqli_query($link,"SET CHARACTER SET utf8");
    //mysqli_query($link,"SET COLLATION_CONNECTION = 'utf8_general_ci'");
    mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
		
		$query="INSERT INTO drone_data(GPS, Yaw, Pitch, Roll, DeviceId) VALUES ('GPS','Yaw','Pitch','Roll','DeviceId')";
		//$query="INSERT INTO drone_data(GPS, Yaw, Pitch, Roll, DeviceId) VALUES ('$GPS','$Yaw','$Pitch','$Roll','$DeviceId')";
	echo("query: $query");
	mysqli_query($link,$query);
	mysqli_close($link);

?>