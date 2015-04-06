/*
  functionality to read the battery status of the mobile phone
  
  2015-03-06: wohl noch keine einheitliche Umsetzung
    chrome unter ubuntu (Notebook:) geht per getbattery
    firefox unter ubuntu (Notebook:) geht nicht per getBattery
    s. auch
    http://caniuse.com/#feat=battery-status
    -> fallback einbauen um den alten standard navigator.battery zu nutzen
     	 
*/


//hilfs-funktion fuer ausgabe 
function batteryLog(text)
{
  $('#outBattery').append('<p>'+text+'</p>');
}



function initializeBattery()
{
  var battery = null;
  if(navigator.getBattery) 
  {
    // API is supported
    // Request battery manager object.
    navigator.getBattery().then(
      function(bat)
      {
        batteryLog('html5 style battery api supported');
        battery = bat; //merke mir das batterie-Objekt
        bindeEvents(battery);
        aktualisiereAnzeige(battery);
      }, 
      function(){batteryLog('html5 battery api supported, but unknown error')});	//misserfolg, keine Ahnung      
  } 
  else 
  {
    // API is not supported, fail gracefully.
    batteryLog("html5 Battery Status not supported, try old style");
    battery =  navigator.battery || navigator.webkitBattery || navigator.mozBattery;
    if (battery)
    {
      batteryLog("old style battery api supported");
      bindeEvents(battery);
      aktualisiereAnzeige(battery);
    }
    else
      batteryLog("no support for battery api"); 
  }  
}


//binde die events fuer Aenderung des Akkuzustands
function bindeEvents(battery)
{
  battery.addEventListener("chargingchange", function(e) {
    aktualisiereAnzeige(battery);
  });
  battery.addEventListener("chargingtimechange", function(e) {
    aktualisiereAnzeige(battery);
  });
  battery.addEventListener("dischargingtimechange", function(e) {
    aktualisiereAnzeige(battery);
  });
  battery.addEventListener("levelchange", function(e) {
    aktualisiereAnzeige(battery);
  });
}

//aktualisiere Anzeige
function aktualisiereAnzeige(battery)
{
			 $('#pWirdGeladen').text(battery.charging);  
			 $('#pAkkustand').text(battery.level * 100 +" %");    
			 $('#pEntladezeit').text(battery.dischargingTime);  
			 $('#pLadezeit').text(battery.chargingTime);  
}