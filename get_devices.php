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
	$link = mysqli_connect("localhost", "malon_quadcopter", "85sGe5p@")or die ("cannot connect"); //DB, user, pass
	//$currentuser = wp_get_current_user();
	//$currentuserID = $currentuser->ID;
	//echo $currentuserID;
	//$user = $userID;
	//echo($userID);
	mysqli_select_db($link,'malonslaught_quadcopter') or die("error selecting db");
	$query="select *from drone_devices where find_in_set('$userid',owner) >0";
	$result = mysqli_query($link,$query);
	//$num_rows = mysqli_num_rows($result);
	/*echo "<table>
	<tr>
	<th>id</th>
	<th>Device name</th>
	<th>owner</th>
	<th>Latitude</th>
	<th>Longitude</th>
	</tr>";
	while($row = mysqli_fetch_array($result)) {
	echo "<tr>";
	echo "<td>" . $row['id'] . "</td>";
	echo "<td>" . $row['name'] . "</td>";
	echo "<td>" . $row['owner'] . "</td>";
	echo "<td>" . $row['devlat'] . "</td>";
	echo "<td>" . $row['devlong'] . "</td>";
	echo "</tr>";
}
	echo "</table>";*/
	//echo "$num_rows Rows\n";
	if ($result->num_rows > 0) {
		// output data of each row
		//$mydevices = array();
		//$index = 0;
		/*for($x = 0; $x < $result; $x++) {
				$mydevices[$x] = $row["name"];
			}*/
		while($row = $result->fetch_assoc()) {
			//$mydevices[$index] = $row["name"];
			echo $row["name"]."<br>";
			?>
			<label class="switch"> <input id="connect_button" src="<?php echo $row["name"]; ?>" type="checkbox" onclick="myFunction(document.getElementById('connect_button').getAttribute('src'))"> 
				<span class="slider round"></span></label><br>
			<?php
			//$index++;
		}
	} else {
		echo "Δεν βρέθηκε καταχωρημένη συσκευή";
		//echo($result_);
	}?>
	<?php
	mysqli_close($link);
	?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script>
		function myFunction() {
			//selectedDevice.innerHTML.src;
			//alert(selectedDevice);
			var markers = {};
			var map;
			var myOptions = {
				zoom: 13,
				center: new google.maps.LatLng(38.037709, 23.746697),
				mapTypeId: 'roadmap'
			};
			map = new google.maps.Map($('#map')[0], myOptions);			
			var switchStatus = false;
			$("#connect_button").on('change', function() {
			if ($(this).is(':checked')) {
				switchStatus = $(this).is(':checked');
				var device_names = document.getElementById("connect_button").getAttribute("src");
				device_names = JSON.stringify({device_names:device_names});
				var oldLong, oldLati = "";
				var gmarkers = [];
				const interval = setInterval(function(){
					$.ajax({
  						method: "POST",
  						url: "/get_device_id.php",
						contentType: 'application/json',
  						data: device_names,
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
						},
						error: function(result){
							//clearInterval(interval);
							alert("Could not connect to device");
							clearInterval(interval);
						}		
					})
				}, 2000);
			}
			else {
				//switchStatus = $(this).is(':checked');
				//var myLatLng = { lat: 38.037709, lng: 23.746697 }; 
				//var markerId = getMarkerUniqueId(38.037709, 23.746697); // get marker id by using clicked point's coordinate
					//var marker = markers[markerId]; // find marker
					//removeMarker(marker, markerId);
				console.log(switchStatus);// To verify
				//clearInterval(interval);
				}
				
			});
			/*var myLatLng = { lat: 38.037709, lng: 23.746697 }; 
			new google.maps.Marker({
				position: myLatLng,
				map,
				title: "Hello World!",
				});
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				alert("Η σύνδεση με τη συσσκευή ήταν επιτυχής");
				//document.getElementById("demo").innerHTML = this.responseText;
				}
			};
			xhttp.open("POST", "100.85.110.117:12345", true);
			xhttp.send();*/
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