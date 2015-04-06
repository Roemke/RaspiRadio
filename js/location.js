
/* Erläuterungen (englisch)
	   http://www.w3schools.com/html/html5_geolocation.asp
	 deutsch: 
		http://de.wikipedia.org/wiki/W3C_Geolocation_API
		http://www.selfhtml5.org/2014-html5-features/standort-im-browser-ermitteln-per-html5-geolocation-api/
*/

var myPosition = null; //merke mir die Postion global
var map = null ; //und die Karte
var markerAktuell; //und den Marker fuer die Position

function initializeLocation()
{
	if (navigator.geolocation) 
	{
    var options = {
      enableHighAccuracy: true,   //gps verwenden so vorhanden
    }
    navigator.geolocation.getCurrentPosition(showPosition, showError, options);
	} 
	else 
	{
    alert('Ihr Browser unterstützt die W3C Geolocation API nicht.'); 
	}
}

function showPosition(position) { //firebug oeffnen und console anschauen
  myPosition = position;  
	console.log(
        'Die Geoposition dieses Geräts ist (Stand: ' + new Date(position.timestamp).toLocaleTimeString() + '):\n'+
        'Breitengrad: ' + position.coords.latitude + '° \n'+
        'Längengrad: ' + position.coords.longitude + '° \n'+
        '  Genauigkeit: ' + position.coords.accuracy + 'm\n' +
        (position.altitude ? ('Höhe: ' + position.coords.altitude + 'm\n' +
                                    '  Genauigkeit: ' + position.cooords.altitudeAccuracy + 'm') : "")
    ) ;

		createMap();
}

//nur kopiert, nicht getestet :-)
function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            console.log('Sie haben die Abfrage ihrer Geoposition untersagt.');
            break;
        case error.POSITION_UNAVAILABLE:
            console.log('Es sind keine Geopositionsdaten verfügbar.');
            break;
        case error.TIMEOUT:
            console.log('Das Timeout für die Ortsanfrage wurde überschritten.');
            break;
        default:
            console.log('Es ist ein unbekannter Fehler aufgetreten (#' + error.code + ': ' + error.message + ')');
            break;
    }
}

//Karte erzeugen
function createMap() {
	var myLatlng = new google.maps.LatLng(myPosition.coords.latitude, myPosition.coords.longitude);
	/* Welche kartentypen sollen da sein:
	 Build list of map types.
	 You can also use var mapTypeIds = ["roadmap", "satellite", "hybrid", "terrain", "OSM"]
	 but static lists sucks when google updates the default list of map types.
	 */
	var mapTypeIds = [];
	for (var type in google.maps.MapTypeId) {
		mapTypeIds.push(google.maps.MapTypeId[type]);
	}
	//ich moechte ausserdem OpenStreetMap
	mapTypeIds.push("OSM");
	var minZoomLevel = 20;
	map = new google.maps.Map(document.getElementById('divMap'), {
		zoom : minZoomLevel,
		center : myLatlng,
		mapTypeId : "terrain",
		mapTypeControlOptions : {
			mapTypeIds : mapTypeIds
		},
	});
	map.mapTypes.set("OSM", new google.maps.ImageMapType({
		getTileUrl : function(coord, zoom) {
			return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
		},
		tileSize : new google.maps.Size(256, 256),
		name : "OpenStreetMap",
		maxZoom : 18
	}));
	markerAktuell = new google.maps.Marker({
		position : myLatlng,
		map : map,
		icon : {
			path : google.maps.SymbolPath.CIRCLE,
			scale : 5
		},
		title : 'aktuell'
	});
}

