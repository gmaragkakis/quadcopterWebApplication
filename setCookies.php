<?php
header('Content-Type: text/html; charset=utf8mb4_unicode_ci'); 
//$visit_time = date('F j, Y  g:i a');
echo("visitor!");
if(!isset($_COOKIE[$wordpress_logged_in_f3849f9f6f5cc4a26f80209c5658a162])) {
 $user_login = $_COOKIE['wordpress_logged_in_f3849f9f6f5cc4a26f80209c5658a162'];
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
			echo $row["ID"];
		}
	}
 mysqli_close($link);
}
else{echo("visitor!");}
 
?>