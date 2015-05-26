/*
  mobile interface
  set timeout to the functions because the requests are sometimes
  very long running 
  I don't understand why, maybe problem is related to sse (seems so)
  but I'm not sure maybe I should use an update of state on the client 
  side if the actual window is shown so do all with polling  
*/


var globalState = {
 name: '',
 title: '',
 vol: '',
 page: '',
 actualInitialized: false
};

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
      globalState.page = 2;
      stationsActualize();//ajax request absetzen, siehe js/stations.js
     break;
    case "aktuellPage":
      globalState.page = 1; 
      aktuellActualize(); 
    break;
    case "helpPage":
      globalState.page = 3;
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
  Actualize.stop();
  $.get('php/ajaxSender.php', {action: 'showStations' }, function(data){});
  headerReset();
  $('#senderList').html('');
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
    Actualize.start();
  });	
}
//----------------------------------------
//aktuell gespielt
function aktuellActualize()
{
 Actualize.stop();
 $.get('php/ajaxSender.php', {action: 'showActual' }, function(data){});
 //combined status and current
 $.ajax({url:"php/ajaxSender.php",data: {action: 'statusAndCurrent'}, timeout:3000, success: function(data) {
  //console.log("answer current: " + data);
  handleStatusCurrent(data);
  Actualize.start();
 }});
}

function handleStatusCurrent(data)
{
  //console.log(data);		
  data = $.parseJSON(data);
  headerReset();
  if (data.state == 1) //fehler
     headerMessage("Error: " + data.infoText); 
  else
  {
    var name = data.result[1].values.Name; //Sender und weitere Informationen
    var title = data.result[1].values.Title; //gerade gespielt
    var vol = parseFloat(data.result[0].values.volume);
    vol = (vol - 50 )*2; //50 % on mpd is nothing
    if (globalState.vol != vol)
    {
      $('#volSlider').val(vol).slider('refresh');
      $('#volNumber').html(vol);
    }
    //console.log("title: " + title + " name: " + name + "  Volume " + vol);
    if (typeof(title) == "undefined" )
     title = " &ndash; " ;
    if (typeof(name) == "undefined")
     name = "&ndash;";
    if (globalState.name != name)
     $('#name').html(name); 
    if (globalState.title != title)
     $('#title').html(title);
     
    globalState.title = title;
    globalState.name = name;
    globalState.vol = vol;
  }
}
//standard event fuer Einstellungen, vergleichbar mit document.ready ist 
//der Pagecreate-Event
//Event reihenfolge:
//https://jqmtricks.wordpress.com/2014/03/26/jquery-mobile-page-events/
//http://www.gajotres.net/page-events-order-in-jquery-mobile-version-1-4-update/
$(document).on('pagecreate','#stationsPage', function() 
{
 Actualize.start();
 $('#senderList').on('click', 'li', function() {
    $.get("php/ajaxSender.php",{action: 'switch', station:  $(this).attr('id').replace("sender","")}, function(data)
       {
         //console.log("answer of switch: " + data); 
       });
    //change page to aktuell 
    //$(document.body).pagecontainer('change',$('#aktuellPage'),{transition: 'slideup'});
  });   
}); 

$(document).on('pagecreate','#aktuellPage', function() 
{
 globalState.actualInitialized = true; 
 Actualize.start();
 $("#volSlider").on( "slidestop", function( event, ui ) {
  var vol = parseInt($('#volSlider').val());
  var volCor = parseInt(50 + vol/2);
  //console.log("vol Cor is " + volCor);
  $('#volNumber').html(vol);
    $.get("php/ajaxSender.php",{action: 'volume', value: volCor }, function(data)
       {
       });
 });//volslider
 $('#bPlay').on('click',function(event,ui)
   {
    $.get("php/ajaxSender.php",{action: 'play' });
   });	
 $('#bStop').on('click',function(event, ui)
   {
    $.get("php/ajaxSender.php",{action: 'pause' }); //stop means pause on radio
   });
 $('#bOff').on('click',function(event, ui)
   {
    $.get("php/ajaxSender.php",{action: 'off' }); //ausschalten
   });   
});

//daten aktualisieren
var Actualize = new function()
{
  var id = 0;
  var ajaxRequest = 0;
  this.start = function()
  {
   if (id == 0)
    id = setTimeout(request, 1000);
  }
  this.stop = function()
  {
   if (id != 0)
   {
    if (ajaxRequest !=0)
     ajaxRequest.abort();
    clearTimeout(id);
   }
   id = 0;
  } 
  
  function request()
  {
   ajaxRequest=$.get("php/ajaxSender.php",{action:'completeState'}, function(data)
   {
    //console.log("answer: " + data);
    if (globalState.actualInitialized)
     handleStatusCurrent(data);
    data = JSON.parse(data);
    var page = data.result[2];
    //console.log("page " +page);
    if (globalState.page != 3 && globalState.page != page)
    {
      if (page == 1)
      {
        $(document.body).pagecontainer('change',$('#aktuellPage'),{transition: 'slideup'});
        //console.log("switch to page 1");
      }
      else if (page == 2)
      {
        $(document.body).pagecontainer('change',$('#stationsPage'),{transition: 'slideup'});
        //console.log("switch to page 2");    
      }
      globalState.page = page;
    }
    id = setTimeout(request,1000);    
   });
  }
}
