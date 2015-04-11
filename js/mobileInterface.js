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
      headerMessage("Don't understand - we have no result from ajax call - you should never be here, report a bug");     
  });	
}
//----------------------------------------
//aktuell gespielt
function aktuellActualize()
{
 $.get('php/ajaxSender.php', {action: 'showActual' }, function(data){});
 //$.get("php/ajaxSender.php", {action: 'status'}, function(data)
 
 $.ajax({url:"php/ajaxSender.php",data: {action: 'status'}, timeout:3000, success: function(data)
 { 
  //console.log("answer is " + data);
  data = $.parseJSON(data);
  //console.log(data.result.values + " type " + typeof(data.result.values));
  var vol = parseFloat(data.result.values.volume);
  //console.log("Vol is " + vol);
  	
 }});

 //$.get("php/ajaxSender.php",{action:'currentSong'}, function(data) {
 $.ajax({url:"php/ajaxSender.php",data: {action: 'currentSong'}, timeout:3000, success: function(data) {
  //console.log("answer current: " + data);
  data = $.parseJSON(data);
  var name = data.result.values.Name; //Sender und weitere Informationen
  var title = data.result.values.Title; //gerade gespielt
  
  //var values = $.parseJSON(data).result.values;
  //var title = values[1];
  //var name = values[2];
  //console.log("title: " + title + " name: " + name);
 }});
 
}
//standard event fuer Einstellungen, vergleichbar mit document.ready ist 
//der Pagecreate-Event
//Event reihenfolge:
//https://jqmtricks.wordpress.com/2014/03/26/jquery-mobile-page-events/
//http://www.gajotres.net/page-events-order-in-jquery-mobile-version-1-4-update/
$(document).on('pagecreate','#stationsPage', function() 
{
 ServerSentEvents.init();
 $('#senderList').on('click', 'li', function() {
    //console.log($(this).attr('id'));
    //following is not needed
    //$('#senderList').data('sender',$(this).attr('id').replace("sender","")); //attach selected Index to #senderList
    //console.log($('#senderList').data('sender')); 
    
    //switch mpd
    $.get("php/ajaxSender.php",{action: 'switch', station:  $(this).attr('id').replace("sender","")}, function(data)
       {
         console.log("answer of switch: " + data); 
       });
    //change page to aktuell 
    $(document.body).pagecontainer('change',$('#aktuellPage'),{transition: 'slideup'});
  });   
}); 

$(document).on('pagecreate','#aktuellPage', function() 
{
  ServerSentEvents.init();
});
//handle server sent events
// sse.php sends messages with text/event-stream mimetype.
var ServerSentEvents = new function() {
  var source = 0 ; // is it a class or is it just a closure :-)?

  /*
  function closeConnection() {
    source.close();
    console.log('> Connection was closed');
  }*/
  
  this.init = function()
  {
    return;
    if (source ==0)
    {
      source = new EventSource('php/sse.php');
      source.addEventListener('message', function(event) {
        console.log(event.data);
        var data = JSON.parse(event.data);
      }, false);

      source.addEventListener('open', function(event) {
        console.log('> Connection was opened');
      }, false);

      source.addEventListener('error', function(event) {
        if (event.eventPhase == 2) { //EventSource.CLOSED - will reconnect
          console.log('> Connection was closed');
        }
      }, false);
    } //eo source ==0
  }//end of init();
};