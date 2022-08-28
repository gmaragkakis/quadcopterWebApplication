<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
</head>
<body>

 
<?php
	header('Content-Type: text/html; charset=utf8mb4_unicode_ci');
	if(!isset($_COOKIE[$wordpress_logged_in_f3849f9f6f5cc4a26f80209c5658a162])) {
		$user_login = $_COOKIE['wordpress_logged_in_8de153c5c34729b1259f7c633a7cc8d8'];
		$user_login = explode("|", $user_login, 2);
		$user_login = $user_login[0];
		// set a cookie for 1 year
		//setcookie('wpb_visit_time', $visit_time, time()+31556926);
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
	$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query="select *from drone_devices where find_in_set('$userid',owner) >0";
	$result = mysqli_query($link,$query);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo $row["name"]."<br>";
			?>
			<label class="switch"> <input id="connect_button" 
				src="<?php echo $row["name"]; ?>" 
				type="checkbox" 
				onclick="myFunction(document.getElementById('connect_button').getAttribute('src'))"> 
				<span class="slider round"></span></label><br>
			<?php
		}
	} else {
		echo "Δεν βρέθηκε καταχωρημένη συσκευή";
	}?>
	<?php
	mysqli_close($link);
	?>

	<label id="Yaw">Yaw: </label><br>
	<label id="Pitch">Pitch: </label><br>
	<label id="Roll">Roll: </label><br>
	<label id="Altitude">Altitude: </label><br>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		function myFunction() {
			var markers = {};
			var map;
			var myOptions = {
				zoom: 13,
				center: new google.maps.LatLng(38.037709, 23.746697),
				mapTypeId: 'roadmap'
			};
			map = new google.maps.Map($('#map')[0], myOptions);	

			var connectcmd = "";
			var device_names = "";

			$("#connect_button").on('change', function() {
			if ($("#connect_button").is(':checked')) {
				switchStatus = $("#connect_button").is(':checked');
				console.log(switchStatus);
				device_names = document.getElementById("connect_button").getAttribute("src");
				connectcmd = "/connect";
				jsonBody = JSON.stringify({device_names:device_names, connectcmd:connectcmd});
				var oldLong, oldLati = "";
				var gmarkers = [];
				const interval = setInterval(function(){
					$.ajax({
  						method: "POST",
  						url: "/get_device_id.php",
						contentType: 'application/json',
  						data: jsonBody,
						dataType: 'json',
						success: function(result){
							var coordinates = result[0]["GPS"].split(',');
							console.log(coordinates);
							var Long = parseFloat(coordinates[0]);
							var Lati = parseFloat(coordinates[1]);
							var myLatLng = { lat: Long, lng: Lati };
						    var deviceName = document.getElementById("connect_button");
								if(oldLong != Long || oldLati != Lati){
									var marker = new google.maps.Marker({
												position: myLatLng,
												//icon: svgMarker,
												//label: "AirHunter",
												map,
												title: deviceName.getAttribute("src"),
												});
									// Push your newly created marker into the array:
									gmarkers.push(marker);
								}
								else if(oldLong == Long && oldLati == Lati){
									console.log("same coordinates");
									for(i=0; i<gmarkers.length-1; i++){
        								gmarkers[i].setMap(null);
    								}
								}			
							
							oldLong = Long;
							oldLati = Lati;

							var Yaw = result[0]["Yaw"];
							console.log(Yaw);
							if(switchStatus){
								document.getElementById('Yaw').innerHTML = 'Yaw: ' + Yaw;
							}

							var Pitch = result[0]["Pitch"];
							console.log(Pitch);
							if(switchStatus){
								document.getElementById('Pitch').innerHTML = 'Pitch: ' + Pitch;
							}

							var Roll = result[0]["Roll"];
							console.log(Roll);
							if(switchStatus){
								document.getElementById('Roll').innerHTML = 'Roll: ' + Roll;
							}

							var Altitude = result[0]["Altitude"];
							console.log(Altitude);
							if(switchStatus){
								document.getElementById('Altitude').innerHTML = 'Altitude: ' + Altitude;
							}
						},
						error: function(result){
							alert("Could not connect to device");
							document.getElementById('connect_button').click();
							clearInterval(interval);
						}		
					})
					switchStatus = $("#connect_button").is(':checked');
					console.log(switchStatus);
					if(!switchStatus){clearInterval(interval);}
				}, 2000);
			}
			else {
				device_names = document.getElementById("connect_button").getAttribute("src");
				connectcmd = "/disconnect";
				switchStatus = $("#connect_button").is(':checked');
				console.log(switchStatus);// To verify
				jsonBody = JSON.stringify({device_names:device_names, connectcmd:connectcmd});
				$.ajax({
  						method: "POST",
  						url: "/get_device_id.php",
						contentType: 'application/json',
  						data: jsonBody,
						dataType: 'json',
						success: function(result){
							document.getElementById('Yaw').innerHTML = 'Yaw: ';
							document.getElementById('Pitch').innerHTML = 'Pitch: ';
							document.getElementById('Roll').innerHTML = 'Roll: ';
							document.getElementById('Altitude').innerHTML = 'Altitude: ';
							console.log("Disconnect from device successfully");
						},
						error: function(result){
							alert("Could not disconnect from device");
						}		
					})
				}
				
			});
		}

		function drawDeviceMarker(data){
			
			alert(data);
		}

		var getMarkerUniqueId= function(myLatLng) {
			return myLatLng;
		}
		var removeMarker = function(marker, markerId) {
			marker.setMap(null); // set markers setMap to null to remove it from map
			delete markers[markerId]; // delete marker instance from markers object
		};
	</script>
</body>
</html>