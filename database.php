<?php
	header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
	$servername = "localhost";
	$username = "malon_quadcopter";
	$password = "85sGe5p@";
	$db="malonslaught_quadcopter";
	$conn = mysqli_connect($servername, $username, $password,$db);
	echo("Hallo");
?>