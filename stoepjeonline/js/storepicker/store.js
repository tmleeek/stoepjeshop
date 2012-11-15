if (typeof(Number.prototype.toRad) === "undefined") {
  Number.prototype.toRad = function() {
    return this * Math.PI / 180;
  }
}

function Store(data) {
	this.storepickerMap = data.storepickerMap;

	this.id = data.id;
	this.lat = data.lat;
	this.lng = data.lng;
	this.name = data.name;
	this.pickup = data.pickup;
	this.send = data.send;
	this.sendradius = data.sendradius;
	this.content = data.content;
}

Store.prototype.calculateDistance = function(lat, lng) {
	var lat1 = lat.toRad();
	var lng1 = lng.toRad();
	var lat2 = this.lat.toRad();
	var lng2 = this.lng.toRad();

	var R = 6371; // km

	// SLOW EXACT
/*
	var d = Math.acos(Math.sin(lat1)*Math.sin(lat2) + 
					  Math.cos(lat1)*Math.cos(lat2) *
					  Math.cos(lng2-lng1)) * R;
*/

	// FAST APPROX
	var x = (lng2-lng1) * Math.cos((lat1+lat2)/2);
	var y = (lat2-lat1);
	var d = Math.sqrt(x*x + y*y) * R;

	this.distance = d;
}

Store.prototype.putMarker = function() {
	if (typeof(this.marker) == 'undefined') {
		var storepickerMap = this.storepickerMap;

		var latlng = new google.maps.LatLng(this.lat, this.lng);

		var infowindow = new google.maps.InfoWindow({
			content: this.content
		});

		var marker = new google.maps.Marker({
			position: latlng, 
			map: storepickerMap,
			title: this.name
		});   

		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(storepickerMap, marker);
		});

		this.marker = marker;
		this.infowindow = infowindow;
	} else if (typeof(this.marker) == 'object') {
		this.marker.setVisible(true);
	}
}

Store.prototype.removeMarker = function() {
	if (typeof(this.marker) == 'object') {
		this.marker.setVisible(false);
	}
}

Store.prototype.centerMarker = function() {
	this.infowindow.open(this.storepickerMap, this.marker);
}

Store.prototype.closeInfowindow = function() {
	this.infowindow.close();
}
