<?php
//echo "Study " . $_GET['DeviceID'] . " at " . $_GET['MACAddress'];
$DeviceId = $_GET['DeviceID'];
$MACAddress = $_GET['MACAddress'];
echo $DeviceId;
echo $MACAddress;
$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
$query = "SELECT ID FROM `drone_devices` WHERE DeviceId ='$DeviceId'";
$result = mysqli_query($link,$query);
if ($result->num_rows > 0) {
	echo 'device found';
}else{
	echo 'device NOT found';
}
mysqli_close($link);
?>