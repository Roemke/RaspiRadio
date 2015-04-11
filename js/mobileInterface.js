/*
  mobile interface
  set timeout to the functions because the requests are sometimes
  very long running 
  I don't understand why, maybe problem is related to sse (seems so)
  but I'm not sure maybe I should use an update of state on the client 
  side if the actual window is shown so do all with polling  
*/


function headerMessage(message)
{
   $('[ data-role="header"]').append(message+'<br>'); 
}


//pageshow is deprecated - see gajotres - we have it in common.js
//h(e)ad problems with enhancement of toolbar if I reset the header
//multiple calling of toolbar() seems to be no problem, so use it as workaround
function headerReset()
{
  $('#aktuellPage [ data-role="header"]').html('<h1>Aktuell</h1>').toolbar().toolbar('refresh');
  $('#stationsPage [ data-role="header"]').html('<h1>Sender</h1>').toolbar().toolbar('refresh');
  $('#helpPage [ data-role="header"]').html('<h1>Hilfe / &Uuml;ber</h1>').toolbar().toolbar('refresh');
}

//handle pageshow events
$(document).on('pagecontainershow',function(e,ui)
{
  var pageId = ui.toPage[0].id;
  switch (pageId)
  {
    case "stationsPage":
      stationsActualize();//ajax request absetzen, siehe js/stations.js
     break;
    case "aktuellPage":
      aktuellActualize(); 
    break;
  } 
});

//---------------------------------------------------
/*
  handle stuff around list of stations     	 
*/
//ajax request to update the list of stations
function stationsActualize()
{
  $('#senderList').html('');
  $.get('php/ajaxSender.php', {action: 'showStations' }, function(data){});
  $.get("php/ajaxSender.php", {action: 'liste'}, function(data)
  {
    //console.log("Answer is " + data);
    var result = $.parseJSON(data);
    if (result)
    {
     if ( result.state == 1)
     {//error
       headerMessage("Error: " + result.infoText); 
     }
     else
     {
       for (var i = 0; i < result.result.length; ++i)
       {
        $('#senderList').append('<li id="sender'+(i+1)+'"><a href="#">'+result.result[i].name + '</a></li>'); 
       }
     }
     $('#senderList').listview('refresh');
    }//eo result
    else
      headerMessage("Don't understand - we have no result from ajax call - you should never be here :-)");     
  });	
}
//----------------------------------------
//aktuell gespielt
function aktuellActualize()
{
 $.get('php/ajaxSender.php', {action: 'showActual' }, function(data){});
 //combined status and current
 $.ajax({url:"php/ajaxSender.php",data: {action: 'statusAndCurrent'}, timeout:3000, success: function(data) {
  console.log("answer current: " + data);
  data = $.parseJSON(data);
  var name = data.result[1].values.Name; //Sender und weitere Informationen
  var title = data.result[1].values.Title; //gerade gespielt
  var vol = data.result[0].values.volume;
  $('#volSlider').val(vol).slider('refresh');
  $('#volNumber').html(vol);
  console.log("title: " + title + " name: " + name + "  Volume " + vol);
  if (title == "undefined")
   title = " &ndash; " ;
  if (name == "undefined")
   name = "&ndash;";
  $('#name').html(name); 
  $('#title').html(title); 
 }});
}
//standard event fuer Einstellungen, vergleichbar mit document.ready ist 
//der Pagecreate-Event
//Event reihenfolge:
//https://jqmtricks.wordpress.com/2014/03/26/jquery-mobile-page-events/
//http://www.gajotres.net/page-events-order-in-jquery-mobile-version-1-4-update/
$(document).on('pagecreate','#stationsPage', function() 
{
 $('#senderList').on('click', 'li', function() {
    $.get("php/ajaxSender.php",{action: 'switch', station:  $(this).attr('id').replace("sender","")}, function(data)
       {
         console.log("answer of switch: " + data); 
       });
    //change page to aktuell 
    //$(document.body).pagecontainer('change',$('#aktuellPage'),{transition: 'slideup'});
  });   
}); 

$(document).on('pagecreate','#aktuellPage', function() 
{
 $("#volSlider").on( "slidestop", function( event, ui ) {
  var vol = $('#volSlider').val();
  $('#volNumber').html(vol);
    $.get("php/ajaxSender.php",{action: 'volume', value: vol }, function(data)
       {
       });
 } );
});
