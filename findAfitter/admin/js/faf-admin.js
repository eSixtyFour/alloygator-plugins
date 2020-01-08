
var e64_locMap, e64_geocoder, e64_mapPin, e64_marker, e64_mapApiLoaded=false;
var alloyGator = {lat: 52.29023122098705, lng: -1.8895136355820341};

if (!$) $=jQuery;

(function($) {
	
	if ($("#faf_five-star-rated").val()=="Y") {
		$(".five-star-rating").addClass("is-five-star");
	}
	
	$("#faf_find_fitter").click(function(e) {
		e.preventDefault();
		findFitterAddress();
	});
	
	$(".five-star-rating").click(function(e){
		e.preventDefault();
		toggleFiveStar();
	})
	
	$("#e64_faf_enable_find_a_fitter_sales_funnels").on("click change", function(e) {
		checkSalesFunnelGroup();
	});
	
	checkSalesFunnelGroup();
	
})(jQuery);

function checkSalesFunnelGroup() {
	if ($("#e64_faf_enable_find_a_fitter_sales_funnels").is(":checked")) {
		$(".e64-group-salesFunnel").show();
	}
	else {
		$(".e64-group-salesFunnel").hide();
	}
}

function adminInitMap() {
	if (!e64_mapApiLoaded) {
		alert('Google Maps API has not loaded correctly.\n\nPlease contact the website administrator.');
	}
	else {
		mZoom = 5;
		if (!isNaN(php_vars.mapZoom)) mZoom = parseInt(php_vars.mapZoom);
		
		var fitter = alloyGator;
	
		e64_geocoder = new google.maps.Geocoder;
	
		e64_locMap = new google.maps.Map(document.getElementById('locMap'), {
			center: fitter,
			zoom: mZoom
		});
		
		e64_mapPin = {
			url: php_vars.adUrl+'/admin/assets/map-pin-sd.png',
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(32,66)
		};
		
		setFitterLocationMarker();
	}
}

var e64_adminMapsLoaded = function() {
	e64_mapApiLoaded = true
	adminInitMap();
};


var setFitterLocationMarker = function() {
	if (e64_initLat != 0 && e64_initLng != 0) {
		
		$latLng = {lat:e64_initLat,lng:e64_initLng};
		
		e64_zoom = (e64_zoom == 0) ? 15 : e64_zoom;
		
		e64_locMap.setCenter($latLng);
		e64_locMap.setZoom(e64_zoom);
		
		$("#faf_longitude").val(e64_initLng);
		$("#faf_latitude").val(e64_initLat);
		
		e64_marker = new google.maps.Marker({
			map: e64_locMap,
			icon: e64_mapPin,
			draggable: true,
			animation: google.maps.Animation.DROP,
			position: $latLng
		});
	}
}

var findFitterAddress = function() {
	//https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=YOUR_API_KEY
	
	if (!e64_mapApiLoaded) {
		alert('Google Maps API has not loaded correctly.\n\nPlease confirm your API is valid and check the\nbrowser console window for any reported errors.');
	}
	else {
		formValid = true;
		$(".required").each(function(itm){
			if ($(this).val()=="") {
				formValid=false;
			}
		});
		
		if (!formValid) {
			alert('Please complete all required fields\nbefore trying to located the Fitter');
		}
		else {
				
			addr = {'address' : addrLine('faf_house-name-_-no', '+')+addrLine('faf_street-_-road')+addrLine('faf_town')+addrLine('faf_city')+addrLine('faf_postcode')+addrLine('faf_country')};
			
			if (typeof(e64_marker)!=="undefined") e64_marker.setMap(null);
			
			country = $("#faf_country").val();
			pc = $("#faf_postcode").val();
			
			comps = {};
			if (""!=pc) {
				comps.postalCode = pc;
			}
			if (""!=country) {
				comps.country = country; 
			}
			addr.componentRestrictions=comps;
			
			e64_geocoder.geocode( addr, function(results, status) {
				if (status == 'OK') {
					e64_locMap.setCenter(results[0].geometry.location);
					e64_locMap.setZoom(18);
					
					e64_initLat = results[0].geometry.location.lat();
					e64_initLng = results[0].geometry.location.lng();
					
					$("#faf_latitude").val(e64_initLat);
					$("#faf_longitude").val(e64_initLng);
					
					e64_marker = new google.maps.Marker({
						map: e64_locMap,
						icon: e64_mapPin,
						draggable: true,
						animation: google.maps.Animation.DROP,
						position: results[0].geometry.location
					});
					
					google.maps.event.addListener(e64_marker, 'dragend', function(e64_marker) {
						var latLng = e64_marker.latLng;
						currentLatitude = latLng.lat();
						currentLongitude = latLng.lng();
						$("#faf_longitude").val(currentLongitude);
						$("#faf_latitude").val(currentLatitude);
					});	
					
					$(".e64_inithide").removeClass("e64_inithide");
					
				} else {
					alert('Google Maps could not find the address:\n\n'+addr+'\n\nThe reason given was: ' + status);
				}
			});
		}
	}
}

var toggleFiveStar = function() {
	$(".five-star-rating").toggleClass("is-five-star");
	
	if ($(".five-star-rating").hasClass("is-five-star")) {
		$("#faf_five-star-rated").val("Y");
	}
	else {
		$("#faf_five-star-rated").val("N");
	}
}

var addrLine = function(fld, delim) {
	
	if (!delim) delim = ',';
	var part = "";
	var lne = $("#"+fld).val();
	if (lne) {
		if (""!==lne) part = lne.replace(' ', '+')+delim;
	}
	return part;
	
}

