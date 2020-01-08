var e64_locMap, e64_geocoder, e64_inactivePin, e64_activePin, e64_activeStar, e64_inactiveStar, e64_mZoom, latlngbounds, e64_radius, e64_myMarker;
var e64_meterCalc = 1609.344, e64_mapApiLoaded=false, e64_marker=[], e64_mobRadius=null;
var currentLoc = {lat: 52.29023122098705, lng: -1.8895136355820341};
var ag_fitters = JSON.parse(php_vars.fitters);

(function($) {
		
	e64_setClickActions();
		
})(jQuery);


function e64_mapsLoaded() {
	e64_mapApiLoaded = true;
	e64_initMap();
};

function e64_initMap() {
	
	mapDiv = document.getElementById('e64-fitterMap');
	
	if (mapDiv) {
		if (!e64_geocoder) e64_geocoder = new google.maps.Geocoder;
		
		e64_mZoom = (isNaN(php_vars.mapZoom)) ? 12 : parseInt(php_vars.mapZoom);
		
		e64_locMap = new google.maps.Map(mapDiv, {
			center: currentLoc,
			zoom: e64_mZoom
		});
		
		e64_activePin = {
			url: php_vars.adUrl+'/assets/map-pin-active.png',
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(20,39)
		};
	
		e64_inactivePin = {
			url: php_vars.adUrl+'/assets/map-pin-inactive.png',
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(12,23)
		};
	
		e64_activeStar = {
			url: php_vars.adUrl+'/assets/map-star-active.png',
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(20,39)
		};
	
		e64_inactiveStar = {
			url: php_vars.adUrl+'/assets/map-star-inactive.png',
			origin: new google.maps.Point(0, 0),
			anchor: new google.maps.Point(12,23)
		};
		
		e64_setFitterMarkers();
		
	}
}

function e64_setClickActions() {
	$(".fitterSearchForm").on("click", ".e64-active-search .e64_findFitters", function(e) {
		e.preventDefault();
		radius = $(".e64-active-search .e64_radius").val();
		pc = $(".e64-active-search .e64-postcode").val();
		if (!isNaN(radius)&&""!=pc) {
			$(".e64-postcode").val(pc);
			findFitters(pc, parseInt(radius));
		}
	})
	
	
	$(".fitterSearchForm").on("click", ".e64-active-search .e64_useCurrentLocation", function(e) {
		e.preventDefault();
		if (!navigator.geolocation) {
			alert("Geolocation is not supported by this browser.");
		}
		else {
			navigator.geolocation.getCurrentPosition(e64_setCurrentLocation, function(error) {
			if (error.code == error.PERMISSION_DENIED)
				alert("Geolocation is enabled but has been disallowed for this site.\n\nTo use this feature, please change your settings to allow Location for this site.");
			});
		}
	})	

	$(".e64-ftr-accordion").on("click", function(e) {
		e.preventDefault();
		
		crnt = $(".e64-ftr-active").data("target");
		
		ftr_collapsePanel();
		
		pnl = $(this).data("target");
		hgt = $(this).data("height");
		
		if (crnt != pnl) {
			$(this).addClass("e64-ftr-active")
			$(pnl).addClass("e64-ftr-active-pnl").css("max-height", hgt+"px");
		}
	});
	
	$(".e64-mapList").on("click", ".showFitterOnMap", function(e) {
		e.preventDefault();
		
		id = $(this).data("id");
		ftrMrkr = e64_marker[id].marker;
		e64_toggleBounce(ftrMrkr);
		google.maps.event.trigger(ftrMrkr, 'click');
	});
	
}

function e64_setCurrentLocation(position) {
	
	$("#e64-currentLatitude").val(position.coords.latitude);
	$("#e64-currentLongitude").val(position.coords.longitude);
	
	radius = $(".e64-active-search .e64_radius").val();
	
	if (!isNaN(radius)) {
		findFitters("", parseInt(radius));
	}
	
}

function e64_toggleBounce(marker) {
	e64_stopMarkerAnimations();
	marker.setAnimation(google.maps.Animation.BOUNCE);
	window.setTimeout(function(){e64_stopMarkerAnimations();}, 1500);
}

function e64_stopMarkerAnimations() {
	for (var i=0;i<e64_marker.length;i++)
		if (e64_marker[i]) e64_marker[i].marker.setAnimation(null);
}

function ftr_collapsePanel() {
	$(".e64-ftr-active").removeClass("e64-ftr-active");
	$(".e64-ftr-active-pnl").removeClass("e64-ftr-active-pnl").css("max-height", "");
	$(this).find(".e64-hidden").hide();
}


function e64_getLocation() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(e64_showPosition);
	} else {
		x.innerHTML = "Geolocation is not supported by this browser.";
	}
}

function e64_showPosition(position) {
	currentLoc = {lat: position.coords.latitude, lng: position.coords.longitude};
}

function e64_setFitterMarkers() {
	
	if (e64_marker.length == 0) {
		if (!e64_mapApiLoaded) {
			alert('Google Maps API has not loaded correctly.\n\nPlease contact the website administrator.');
		}
		else {
			
			if (!e64_geocoder) e64_geocoder = new google.maps.Geocoder;
			
			for (var iFtr=0;iFtr<ag_fitters.length;iFtr++) {
				
				var ftr = ag_fitters[iFtr];
				
				ftr.arr = iFtr;
				
				if ((!isNaN(ftr.fitterLongitude) && !isNaN(ftr.fitterLatitude)) && (parseFloat(ftr.fitterLongitude)!==0 && parseFloat(ftr.fitterLatitude)!==0)) {

					var mrkLatLng = {lat: parseFloat(ftr.fitterLatitude), lng: parseFloat(ftr.fitterLongitude)};
					e64_setMarkerPin(ftr, mrkLatLng)
				}
				else {
					
					var addr = {'address' : addrLine(ftr.fitterAddrLine1)+addrLine(ftr.fitterAddrLine2)+addrLine(ftr.fitterTown)+addrLine(ftr.fitterCity)+addrLine(ftr.fitterPostcode)+addrLine(ftr.fitterCountry)};
					
					comps = {};
					if (""!=ftr.fitterPostcode) {
						comps.postalCode = ftr.fitterPostcode;
					}
					if (""!=ftr.fitterCountry) {
						comps.country = ftr.fitterCountry; 
					}
					addr.componentRestrictions=comps;
					
					getMarkerLatLong(addr, ftr);
				}
			}
			
		}
	}
	
}


function getMarkerLatLong(a, f) {
	e64_geocoder.geocode(a, function(results, status) {
		if (status == 'OK') {
			e64_setMarkerPin(f, results[0].geometry.location)
		}
		else {
			console.log(status);
		}
	});	
}

function e64_setMarkerPin(mrkObj, loc) {
	
	if (mrkObj) {
		var viewRad = "";
		var btn = "";
		var useFunnel = (php_vars.enableFunnel == 'Y') ? true : false;
		
		if (mrkObj.fiveStarFitter == "Y") {
			mapPin = e64_inactiveStar;
			fiveStarLogo = "<img src='"+php_vars.adUrl+"assets/five-star-fitter.png' class='e64-faf-fivestar' />";
			amp = (php_vars.bookUrl.indexOf("?")<=0) ? "?" : "&";
			btn = (!useFunnel || ""==php_vars.bookBtn) ? "" : "<p class='e64-opt-hidden'><a href='"+php_vars.bookUrl+amp+"fid="+mrkObj.id+"' class='e64_bookFitter e64-faf-button' data-fitterid="+mrkObj.id+">"+php_vars.bookBtn+"</a></p>";
		}
		else {
			mapPin = e64_inactivePin;
			fiveStarLogo = "";
		}
		
		fitterType = ("Mobile"==mrkObj.fitterType) ? "<i class='fa fa-truck'></i>Mobile Service" :  "<i class='fa fa-home'></i> Garage Service";
		
		//	if ('Mobile' == mrkObj.fitterType && !isNaN(mrkObj.radiusCovered)) {
		//		viewRad = '<i class="fa fa-dot-circle"></i>';
		//	}
	
		fitterContact = (""==mrkObj.fitterPhone) ? addInforHref(mrkObj.fitterEmail, 'mailto') : addInforHref(mrkObj.fitterPhone, 'tel') ;
		
		var content = 	'<div>'+
							'<h3 class="e64-faf-title"><a href="#" class="showFitterOnMap" title="Show Fitter on the map" data-id='+mrkObj.arr+'>'+mrkObj.fitterCompany+'<i class="fa fa-eye"></i></a></h3>'+
							'<p class="e64-opt-hidden">'+
								addInfoLne(addrComma(mrkObj.fitterAddrLine1,mrkObj.fitterAddrLine2))+
								addInfoLne(addrComma(mrkObj.fitterTown,mrkObj.fitterCity))+
								addInfoLne(mrkObj.fitterPostcode)+
								addInfoLne(mrkObj.fitterCountry)+
							'</p>'+
							'<p>'+ fitterContact + '</p>'+
							'<p>'+ fiveStarLogo + fitterType + '</p>'+
							btn+
						'</div>';
		
		var mrk = new google.maps.Marker({
			map: e64_locMap,
			icon: mapPin,
			draggable: false,
			zIndex:50,
			name: mrkObj.arr,
			position: loc
		});
		
		var info = new google.maps.InfoWindow({
			content: '<span class="e64_infowin">'+content+'</span>'
		});
		
		google.maps.event.addListener(info,'closeclick', function(){
			hideMobileRadius();
		});
		
		google.maps.event.addListener(mrk, 'click', function(m) {
			closeInfoBoxes();
			
			if ('Mobile'==mrkObj.fitterType && !isNaN(mrkObj.radiusCovered)) {
				e64_mobRadius = e64_mobileRadius(mrkObj.radiusCovered, loc);
			}		
			
			info.open(e64_locMap, mrk);
		});
		
		e64_marker[mrkObj.arr]={marker:mrk, fiveStar:mrkObj.fiveStarFitter, info:info, content:content, type: mrkObj.fitterType, radius: mrkObj.radiusCovered};
	}
}


function closeInfoBoxes() {
	for (var i=0;i<e64_marker.length;i++)
		if (e64_marker[i]) e64_marker[i].info.close();
	
	hideMobileRadius();
}

function hideMobileRadius() {
	if (e64_mobRadius) e64_mobRadius.setMap(null);
}

function addInforHref(txt,ref) {
	rtn="";
	if (""!=txt) rtn = 	'<a href="'+ref+':'+txt+'">'+txt+'</a><br/>'
	return rtn;
}


function addInfoLne(txt) {
	rtn="";
	if (""!=txt) rtn = txt+'<br/>';
	return rtn;
}

function showAllFitters() {
	var bounds = new google.maps.LatLngBounds();
	for (var i = 0; i < e64_marker.length; i++) {
		if (e64_marker[i]) bounds.extend(e64_marker[i].marker.getPosition());
	}
	
	//Center map and adjust Zoom based on the position of all markers.
	e64_locMap.setCenter(bounds.getCenter());
	e64_locMap.fitBounds(bounds);
	
}

function findFitters(pc, rad) {
	//https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=YOUR_API_KEY
	
	if (!e64_mapApiLoaded) {
		alert('Google Maps API has not loaded correctly.\n\nPlease contact the website administrator.');
	}
	else {
		lat = $("#e64-currentLatitude").val();
		lng = $("#e64-currentLongitude").val();
		country = $("#e64-restrictCountry").val();
		
		if (""==pc && !isNaN(lat) && !isNaN(lng)) {
			var currentLatLng = new google.maps.LatLng({lat:parseFloat(lat), lng:parseFloat(lng)}); 
			e64_setMarkerAndCenter(currentLatLng);
			e64_drawRadius(rad);
		}
		else {
			
			pcPat = /\b((?:(?:gir)|(?:[a-pr-uwyz])(?:(?:[0-9](?:[a-hjkpstuw]|[0-9])?)|(?:[a-hk-y][0-9](?:[0-9]|[abehmnprv-y])?)))) ?([0-9][abd-hjlnp-uw-z]{2})\b/i;
			
			addr = {'address' : pc};
			comps = {};
			if (pc.search(pcPat)>=0) {
				comps.postalCode = pc;
			}
			if (""!=country) {
				comps.country = country; 
			}
			addr.componentRestrictions=comps;
			
			e64_geocoder.geocode( addr, function(results, status) {
				if (status == 'OK') {
										
					e64_setMarkerAndCenter(results[0].geometry.location);
					e64_drawRadius(rad);

				} else {
					alert('Google Maps could not find the address:\n\n'+addr+'\n\nThe reason given was: ' + status);
				}
			});
		}
	}
}


function e64_setMarkerAndCenter(latLong, rad) {
	
	if( $(".e64-firstQuery.e64-active-search").length > 0) {
		$(".e64-firstQuery.e64-active-search").removeClass("e64-active-search");
		$(".e64-extra-query").addClass("e64-active-search");
		$(".e64-mapList, .e64-extra-query").show();
		$(".e64-firstQuery").hide();
	}
	
	if (e64_myMarker) e64_myMarker.setMap(null);
	
	e64_myMarker = new google.maps.Marker({
		position: latLong,
		map: e64_locMap,
		draggable: false,
		zIndex:25,
		title: 'Your Location'
	});
	
	e64_locMap.setCenter(latLong);
	
}

function addrLine(val, delim) {
	
	if (!delim) delim = ',';
	var part = "";
	if (val) {
		if (""!==val) part = val.replace(' ', '+')+delim;
	}
	return part;
	
}

function addrComma(val, val2) {
	var part = "";
	if (val && val2) {
		if (""!==val&&""!==val2) part = $.trim(val)+', '+$.trim(val2);
	}
	return part;
}


/* 
 * 
 * Radius controls
 * 
 */

function e64_mobileRadius(miles, center) {
	
	var mobileRad = null;
	
	if (!isNaN(miles) && miles > 0) {
		
		var radius = miles * e64_meterCalc;
		
		mobileRad = new google.maps.Circle({
			strokeColor: '#8d8d8d',
			strokeOpacity: 0.6,
			strokeWeight: 2,
			fillColor: '#cccccc',
			fillOpacity: 0.4,
			map: e64_locMap,
			center: center,
			radius: radius
		});
		
	}
	
	return mobileRad;
}

function e64_drawRadius(miles) {
	
	if (e64_radius) e64_radius.setMap(null);
	
	if (!isNaN(miles) && miles > 0) {
		var radius = miles * e64_meterCalc;
		e64_radius = new google.maps.Circle({
			strokeColor: '#97c938',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#97c938',
			fillOpacity: 0.25,
			editable: true,
			draggable: true,
			map: e64_locMap,
			center: e64_locMap.center,
			radius: radius
		});
		
		//if (e64_myMarker) e64_radius.bindTo('center', e64_myMarker, 'position');
		
		google.maps.event.addListener(e64_radius, 'dragend', function() {e64_fitRadius();});
		
		google.maps.event.addListener(e64_radius, 'radius_changed', function() {
			newRadius = e64_radius.getRadius();
			newMiles = newRadius / e64_meterCalc;
			valSet = false;
			$("#e64_radius_2 option").each(function() {
				if (!valSet && newMiles < parseInt($(this).val())) {
					$("#e64_radius_2").val($(this).val());
					valSet = true;
				}
			});
			if (!valSet) {
				$("#e64_radius_2").val($("#e64_radius :last-child").val());
			}
			e64_fitRadius();
		});
		
		e64_fitRadius();
	}
}


function e64_fitRadius()
{
	var bounds = new google.maps.LatLngBounds();
	var points = new Array();
	
	closeInfoBoxes();
	
	radBndsNE = e64_radius.getBounds().getNorthEast();
	radBndsSW = e64_radius.getBounds().getSouthWest();
	
	points = new google.maps.LatLng(radBndsNE.lat(), radBndsNE.lng());
	bounds.extend(points);
	
	points = new google.maps.LatLng(radBndsSW.lat(), radBndsSW.lng());
	bounds.extend(points);
	
	e64_locMap.setCenter(e64_radius.getCenter());
	e64_locMap.fitBounds(bounds);
	
	e64_markersInRadius();
}


function e64_markersInRadius() {
	geometryObj = google.maps.geometry;
	var listContent="";
	var listItems = [];
	
	if (geometryObj) {
		for (var i=0;i<e64_marker.length;i++) {
			if (e64_marker[i]) {
				mrkObj = e64_marker[i];
				marker = mrkObj.marker;
				
				// var inRadius = google.maps.geometry.poly.containsLocation(markerPos, e64_radius);
				// google.maps.geometry.spherical.computeDistanceBetween(randomMarkers[i].marker.getPosition(), searchArea.getCenter()) <= searchArea.getRadius()
				
				distance = geometryObj.spherical.computeDistanceBetween(marker.getPosition(), e64_radius.getCenter());
				
				if (distance <= e64_radius.getRadius()) {
					if (mrkObj.fiveStar == "Y")
						marker.setIcon(e64_activeStar);
					else
						marker.setIcon(e64_activePin);
					
					listItems.push(["<div class='e64-fitterPanel'>" + mrkObj.content + "</div>", distance]);
				}
				else {
					if (mrkObj.fiveStar == "Y")
						marker.setIcon(e64_inactiveStar);
					else
						marker.setIcon(e64_inactivePin);
				}
			}
		}
		
		listItems.sort(function(a, b) {
			return a[1] - b[1];
		});
		
		var i, entry;
		for (i=0; i<listItems.length; i++) {
			entry = listItems[i];
			// console.log(entry);
			listContent+=entry[0];
		}		
		
		$(".e64-mapList").html(listContent);
		
	}
}


function setCheckoutFittersDelivery() {
	$("#ship-to-different-address-checkbox").parent().find("span").html("Ship direct to fitter");
	$(".e64-default-value input, .e64-default-value select, .e64-default-value checkbox").each(function(){
		val = $(this).data('e64value');
		if (val) {
			$(this).val(val);
			$(this).attr("readonly", true);
		}
	});
	if (!$("#ship-to-different-address-checkbox").is(":checked")) $("#ship-to-different-address-checkbox").trigger("click");
}
