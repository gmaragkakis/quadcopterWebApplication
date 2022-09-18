var drawings = [];
var gmarkers = [];
var map ;
var lat = 43.65654;
var lng = -79.90138;
var polylineArray;
var currentPolygon;
var drawingManager;
var selectedShape;
var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
var selectedColor;
var colorButtons = {};
var RouteArray;
var reached_waypoint;
var current_position;

function clearSelection() {
        if (selectedShape) {
          selectedShape.setEditable(false);
          selectedShape = null;
        }
      }

function handleClick(myRadio) {
					//alert(myRadio);
					drawingManager.setDrawingMode(myRadio);
					//drawingManager.setMap(null);						
					}

function saveSelectedRoute(shape){
				var routeCoordinates = null;
				var routeType = null;
        if($.trim($('#route-title').val()) == ''){
      				alert('Πρέπει να συμπληρώσετε ένα όνομα');
   						}
				else{
					if (selectedShape){
						var routeTitle = document.getElementById("route-title").value;
						if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
							routeType = "polyline";
							var polylineArray = selectedShape.getPath().getArray();
							var jsonObject = {
								RouteData:polylineArray,
								RouteName:routeTitle,
								RouteType:routeType
							};
						}
						else if(selectedShape.type == google.maps.drawing.OverlayType.POLYGON){
                            routeType = "polygon";
							var polygonArray = selectedShape.getPath().getArray();
                            var jsonObject = {
                                RouteData:polygonArray,
								RouteName:routeTitle,
								RouteType:routeType
                            };
						}
						else if(selectedShape.type == google.maps.drawing.OverlayType.RECTANGLE){
							routeType = "rectangle";
							var rectangle = selectedShape.getBounds();
                            var jsonObject = {
                                RouteData:rectangle,
								RouteName:routeTitle,
								RouteType:routeType
                            };
						}
						else if(selectedShape.type == google.maps.drawing.OverlayType.CIRCLE){
							routeType = "circle";
							var circle = (selectedShape.getRadius(),selectedShape.getCenter().lat(),selectedShape.getCenter().lng());
                            var jsonObject = {
                                RouteData:circle,
								RouteName:routeTitle,
								RouteType:routeType
                            };
						}
						var routeDetails = JSON.stringify(jsonObject);
							console.log(routeDetails);
							$.ajax({
  							method: "POST",
  							url: "/route_save.php",
								contentType: 'application/json',
  							data: routeDetails,
								dataType: 'json',
								success: function(data){
								console.log("Ajax call on route_save.php ok");}
							})
						
					}
					else{
						alert('Πρέπει να επιλέξετε μία διαδρομή')
					}
				}
}

function executeSelectedRoute(shape){
	var altitude = document.getElementById("altitude-input").value;
	var device_name = getCookie('connected_devices');
	console.log("Device name: " + device_name);
	if(!isNaN(altitude)){
		if (selectedShape){
		var routeTitle = document.getElementById("route-title").value;
		if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
			routeType = "polyline";
			var polylineArray = selectedShape.getPath().getArray();
			var jsonObject = {
				RouteData:polylineArray,
				Altitude:altitude,
				DeviceName:device_name
				//RouteName:routeTitle,
				//RouteType:routeType
				};
			}
			else if(selectedShape.type == google.maps.drawing.OverlayType.POLYGON){
      	routeType = "polygon";
				var polygonArray = selectedShape.getPath().getArray();
        var jsonObject = {
        	RouteData:polygonArray,
					Altitude:altitude,
					DeviceName:device_name
					//RouteName:routeTitle,
					//RouteType:routeType
          };
			}
			else if(selectedShape.type == google.maps.drawing.OverlayType.RECTANGLE){
				routeType = "rectangle";
				var rectangle = selectedShape.getBounds();
        var jsonObject = {
        	RouteData:rectangle,
					Altitude:altitude,
					DeviceName:device_name
					//RouteName:routeTitle,
					//RouteType:routeType
          };
			}
			else if(selectedShape.type == google.maps.drawing.OverlayType.CIRCLE){
				routeType = "circle";
				var circle = (selectedShape.getRadius(),selectedShape.getCenter().lat(),selectedShape.getCenter().lng());
        var jsonObject = {
        	RouteData:circle,
					Altitude:altitude,
					DeviceName:device_name
					//RouteName:routeTitle,
					//RouteType:routeType
          };
			}
			
			var routeDetails = JSON.stringify(jsonObject);
			console.log("RouteDetails: " + routeDetails);
			
			$.ajax({
  			method: 'POST',
				url: '/send_waypoint.php',
				contentType: 'application/json',
				data: routeDetails,
				dataType: 'json',
				success: function(data){
					console.log("Ajax call on send_waypoint.php ok");
					},
				error: function(e){
				console.log(e);
					}
			})
			function check_waypoint(){
			var jsonObject_ = {
					device_names:device_name,
					connectcmd:'/waypoint',
        	coordinates:RouteArray,
					altitude:altitude
          };
			var data_ = JSON.stringify(jsonObject_);
			console.log("RouteArray: " + data_);
		
			var response = $.ajax({
  			method: 'POST',
				url: '/get_device_id.php',
				contentType: 'application/json',
				data: data_,
				dataType: 'json',
				async: false,
				success: function(text){
					response = text;
					console.log("Ajax call on get_device_id.php ok");
					},
				error: function(e){
				console.log(e);
					}
			}).responseText;
			var obj = JSON.parse(response.replace(/[\[\]']+/g,''));
			current_position = obj.GPS;
			console.log("Current position of device: " + current_position);
			
			//function check_waypoint(){
					var jsonObject_ = {device_names:device_name};
					var data_ = JSON.stringify(jsonObject_);
					console.log("JSON to waypoint_reached.php: " + data_);
					var response_ = $.ajax({
  					method: 'POST',
						url: '/waypoint_reached.php',
						contentType: 'application/json',
						data: data_,
						dataType: 'json',
						async: false,
						success: function(text){
							response = text;
							console.log("Ajax call on waypoint_reached.php ok");
						},
						error: function(e){
							console.log(e);
						}
					}).responseText;
					var obj = JSON.parse(response_);
					var waypoint_to_reach = obj.coordinates;
					RouteArray = obj.coordinates;
					console.log("Waypoint to reach: " + waypoint_to_reach);
					var command = obj.connectcmd;
					if(command.includes("waypoint")){
						var waypoint_array = waypoint_to_reach.split(',');
						var waypoint_latitude = waypoint_array[0].slice(0,-2);
						console.log("Latitude of Waypoint: " + waypoint_latitude);
						var waypoint_longitude = waypoint_array[1].slice(0,-2);
						console.log("Longitude of Waypoint: " + waypoint_longitude);
						var current_position_array = current_position.split(',');
						var current_position_latitude = current_position_array[0].slice(0,-2);
						console.log("Latitude of Current Position: " + current_position_latitude);
						var current_position_longitude = current_position_array[1].slice(0,-2);
						console.log("Longitude of Current Position: " + current_position_longitude);
						if(waypoint_latitude === current_position_latitude && waypoint_longitude === current_position_longitude){ //waypoint was reached
							//code to update DB
							var jsonObject_ = {device_names:device_name};
							var data_ = JSON.stringify(jsonObject_);
							$.ajax({
  								method: 'POST',
									url: '/update_waypoint.php',
									contentType: 'application/json',
									data: data_,
									dataType: 'json',
									async: false,
								success: function(text){
									response = text;
									console.log("Ajax call on update_waypoint.php ok");
									console.log("WAYPOINT REACHED!!!")
								},
								error: function(e){
									console.log(e);
								}
							})
							//code to place marker on waypoint_to_reach
							var latFloat = parseFloat(waypoint_array[0]);
							var longFloat = parseFloat(waypoint_array[1]);
							var myLatLng = { lat: latFloat, lng: longFloat };
							
							const icon = {
    						url: "/flag.png", // url
    						scaledSize: new google.maps.Size(50, 50), // scaled size
    						origin: new google.maps.Point(0,0), // origin
    						anchor: new google.maps.Point(0, 0) // anchor
							};
							var marker = new google.maps.Marker({
								position: myLatLng,
								map: map,
								icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
							})
							console.log("Waypoint was reached!");
						}
						else{console.log("flying towards waypoint");}
					}
					else if(command.includes("endroute")){
						console.log("Route is executed successfully!!!");
						$(document).ready(function(){
							clearInterval();
							return;
						})
					}
			}
	}
	else{
		alert('Πρέπει να επιλέξετε μία διαδρομή');
	}
	}
	else{alert('Το πεδίο ύψος πρέπει να είναι αριθμός');}
	
	$(document).ready(function(){
		setInterval(check_waypoint,1500);
	});
}
function getCookie(name) {
    // Split cookie string and get all individual name=value pairs in an array
    var cookieArr = document.cookie.split(";");
    
    // Loop through the array elements
    for(var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        
        /* Removing whitespace at the beginning of the cookie name
        and compare it with the given string */
        if(name == cookiePair[0].trim()) {
            // Decode the cookie value and return
            //alert(decodeURIComponent(cookiePair[1]));
            return decodeURIComponent(cookiePair[1]);
        }
    }
    
    // Return null if not found
    return null;
}
/*function getCookie(cookieName) {
  let cookie = {};
  document.cookie.split(';').forEach(function(el) {
    let [key,value] = el.split('=');
    cookie[key.trim()] = value;
  })
  return cookie[cookieName];
}
function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}*/
function setSelection(shape) {
        clearSelection();
        selectedShape = shape;
        shape.setEditable(true);
				shape.setDraggable(true);
        selectColor(shape.get('fillColor') || shape.get('strokeColor'));
				if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
					
					// getting shape coordinates
          var v = shape.getPath();
          for (var i=0; i < v.getLength(); i++) {
          	var xy = v.getAt(i);
          	console.log('Cordinate lat: ' + xy.lat().toFixed(7) + ' and lng: ' + xy.lng().toFixed(7));
						if(i==0){
							RouteArray = xy.lat().toFixed(7).toString() + "," + xy.lng().toFixed(7).toString();
							console.log("First coordinate of polyline array: " + RouteArray);
						}
        	}
				}
				else if(selectedShape.type == google.maps.drawing.OverlayType.POLYGON){
					
					// getting shape coordinates
          var v = shape.getPath();
          for (var i=0; i < v.getLength(); i++) {
          	var xy = v.getAt(i);
          	console.log('Cordinate lat: ' + xy.lat().toFixed(7) + ' and lng: ' + xy.lng().toFixed(7));
						if(i==0){
							RouteArray = xy.lat().toString() + "," + xy.lng().toString();
							console.log("First coordinate of polygon array: " + RouteArray);
						}
        	}
				}
				else if(selectedShape.type == google.maps.drawing.OverlayType.RECTANGLE){
					console.log(selectedShape.getBounds().getNorthEast().lat());
					console.log(selectedShape.getBounds());
				}
				else if(selectedShape.type == google.maps.drawing.OverlayType.CIRCLE){
					console.log(selectedShape.getRadius(),selectedShape.getCenter().lat(),selectedShape.getCenter().lng());
				}
      }

function deleteSelectedShape() {
        if (selectedShape) {
          selectedShape.setMap(null);
        }
      }

function deleteAllShape() {
        for (var i=0; i < drawings.length; i++)
        {
          drawings[i].overlay.setMap(null);
        }
        drawings = [];
      }
function selectColor(color) {
        selectedColor = color;
        for (var i = 0; i < colors.length; ++i) {
          var currColor = colors[i];
          colorButtons[currColor].style.border = currColor == color ? '2px solid #789' : '2px solid #fff';
        }
				// Retrieves the current options from the drawing manager and replaces the
        // stroke or fill color as appropriate.
        var polylineOptions = drawingManager.get('polylineOptions');
        polylineOptions.strokeColor = color;
        drawingManager.set('polylineOptions', polylineOptions);

        var rectangleOptions = drawingManager.get('rectangleOptions');
        rectangleOptions.fillColor = color;
        drawingManager.set('rectangleOptions', rectangleOptions);

        var circleOptions = drawingManager.get('circleOptions');
        circleOptions.fillColor = color;
        drawingManager.set('circleOptions', circleOptions);

        var polygonOptions = drawingManager.get('polygonOptions');
        polygonOptions.fillColor = color;
        drawingManager.set('polygonOptions', polygonOptions);
      }

function setSelectedShapeColor(color) {
        if (selectedShape) {
          if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
            selectedShape.set('strokeColor', color);
          } else {
            selectedShape.set('fillColor', color);
          }
        }
      }

function makeColorButton(color) {
        var button = document.createElement('span');
        button.className = 'color-button';
        button.style.backgroundColor = color;
        google.maps.event.addDomListener(button, 'click', function() {
          selectColor(color);
          setSelectedShapeColor(color);
        });

        return button;
      }

function buildColorPalette() {
         var colorPalette = document.getElementById('color-palette');
         for (var i = 0; i < colors.length; ++i) {
           var currColor = colors[i];
           var colorButton = makeColorButton(currColor);
           colorPalette.appendChild(colorButton);
           colorButtons[currColor] = colorButton;
         }
         selectColor(colors[0]);
       }
/*function getLoggedInCookie() {
    var cookie = document.cookie.indexOf('wp-settings-time') !== -1;
		alert(cookie);
    if(cookie){
        alert('Logged in');
    }else{
        alert('Not User');
    }
}*/
function initialize() {
  var myWrapper = $("#wrapper");
  $("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
    myWrapper.one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', 			function(e) {
      // code to execute after transition ends
      google.maps.event.trigger(map, 'resize');
    });
  });
	map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: new google.maps.LatLng(37.9838044,23.6925193),
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          disableDefaultUI: true,
          zoomControl: true
        });
	var polyOptions = {
          	strokeWeight: 0,
          	fillOpacity: 0.45,
          	editable: true
        	};
    			drawingManager = new google.maps.drawing.DrawingManager({
						//drawingMode: google.maps.drawing.OverlayType.MARKER,
          	markerOptions: {
            draggable: true
          },
          polylineOptions: {
            editable: true
          },
          rectangleOptions: polyOptions,
          circleOptions: polyOptions,
          polygonOptions: polyOptions,
          map: map
        });
        
		google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
						drawings.push(e);
            if (e.type != google.maps.drawing.OverlayType.MARKER) {
            // Switch back to non-drawing mode after drawing a shape.
            //drawingManager.setDrawingMode(null);

            // Add an event listener that selects the newly-drawn shape when the user
            // mouses down on it.
            var newShape = e.overlay;
            newShape.type = e.type;
            google.maps.event.addListener(newShape, 'click', function() {
              setSelection(newShape);
            });
            setSelection(newShape);
          }
        });
	
google.maps.event.addListener(drawingManager, 'polygoncomplete', function (polygon) {
      polygon = polygon.getPath().getArray();
			var polygonArray = polygon;
			polygonArray = JSON.stringify(polygonArray);
			console.log(polygonArray);
			$.ajax({
  			method: "POST",
  			url: "/polygon_route_temp.php",
				contentType: 'application/json',
  			data: polygonArray,
				dataType: 'json',
				success: function(data){
				console.log("ok");}
	})
		});

	google.maps.event.addListener(drawingManager, 'rectanglecomplete', function (rectangle) {
      rectangle = rectangle.getBounds();
			var rectangleArray = rectangle;
			rectangleArray = JSON.stringify(rectangleArray);
			console.log(rectangleArray);
			$.ajax({
  			method: "POST",
  			url: "/rectangle_route_temp.php",
				contentType: 'application/json',
  			data: rectangleArray,
				dataType: 'json',
				success: function(data){
				console.log("ok");}
	})
        //alert(polygon.getPath().getArray().toString());
    });
google.maps.event.addListener(drawingManager, 'circlecomplete', function (circle) {
      var radius = circle.getRadius();
			var centerLat = circle.getCenter().lat();
			var centerLng = circle.getCenter().lng();
			var circleArray = JSON.stringify([radius,centerLat,centerLng]);
			console.log(circleArray);
			$.ajax({
  			method: "POST",
  			url: "/circle_route_temp.php",
				contentType: 'application/json',
  			data: circleArray,
				dataType: 'json',
				success: function(data){
				console.log("ok");}
	})
    });
google.maps.event.addListener(polygon, 'click', function(polygon) {
  alert("THIS IS POLYGON");
	//setSelection(this)
});
        // Clear the current selection when the drawing mode is changed, or when the
        // map is clicked.
        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
        google.maps.event.addListener(map, 'click', clearSelection);
				google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
				google.maps.event.addDomListener(document.getElementById('delete-all-button'), 'click', deleteAllShape);
				google.maps.event.addDomListener(document.getElementById('save-route'), 'click', saveSelectedRoute);
				google.maps.event.addDomListener(document.getElementById('execute-route'), 'click', executeSelectedRoute);
				//google.maps.event.addListener(drawingManager,'click',setSelection);
				
buildColorPalette();
	//getLoggedInCookie();
	/*$.ajax({
  			method: "POST",
  			url: "/setCookies.php",
				//contentType: 'application/json',
  			//data: polygonArray,
				//dataType: 'json',
				data:{"getUser":"result"},
				success: function(result){
				alert('Καλώς ήρθες ' + result);
				}
	})*/
}

setTimeout(function(){
	google.maps.event.addListener(drawingManager, 'polylinecomplete', function(line) {
	//alert(line.getPath().getArray().toString());
	polyline = line.getPath().getArray();
		var polylineArray = polyline;
		polylineArray = JSON.stringify(polylineArray);
		console.log(polylineArray);
		$.ajax({
  		method: "POST",
  		url: "/polyline_route_temp.php",
			contentType: 'application/json',
  		data: polylineArray,
			dataType: 'json',
			success: function(data){
			console.log("AJAX call ok");}
})
	});
	},1000);
google.maps.event.addDomListener(window, 'load', initialize);
google.maps.event.addListener(map, 'click', function() {
    infowindow.close();
  });
  // Add markers to the map
  // Set up three markers with info windows 
  // add the points    
  var point = new google.maps.LatLng(43.65654, -79.90138);
  var marker = createMarker(point, "This place", "Some stuff to display in the<br>First Info Window")

  var point = new google.maps.LatLng(43.91892, -78.89231);
  var marker = createMarker(point, "That place", "Some stuff to display in the<br>Second Info Window")

  var point = new google.maps.LatLng(43.82589, -78.89231);
  var marker = createMarker(point, "The other place", "Some stuff to display in the<br>Third Info Window")
	
var infowindow = new google.maps.InfoWindow({
  size: new google.maps.Size(150, 50)
});

// This function picks up the click and opens the corresponding info window
function myclick(i) {
  google.maps.event.trigger(gmarkers[i], "click");
}

// A function to create the marker and set up the event window function 
function createMarker(latlng, name, html) {
  var contentString = html;
  var marker = new google.maps.Marker({
    position: latlng,
    map: map,
    zIndex: Math.round(latlng.lat() * -100000) << 5,
		//animation: google.maps.Animation.DROP
		//setIcon:'/flag.png'
  });
	marker.setIcon('/flag.png');
alert('marker');
  google.maps.event.addListener(marker, 'click', function() {
    infowindow.setContent(contentString);
    infowindow.open(map, marker);
		alert('marker');
  });
  // save the info we need to use later for the side_bar
  gmarkers.push(marker);
  // add a line to the side_bar html
  var sidebar = $('#side_bar');
  var sidebar_entry = $('<li/>', {
    'html': name,
    'click': function() {
      google.maps.event.trigger(marker, 'click');
    },
    'mouseenter': function() {
      $(this).css('color', 'red');
    },
    'mouseleave': function() {
      $(this).css('color', '#999999');
    }
  }).appendTo(sidebar);
}
