/*
  handle stuff around list of stations     	 
*/


function headerMessage(message)
{
   $('[ data-role="header"]').append(message+'<br>'); 
}

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
      stationsActualize();//ajax request absetzen
    break;
  } 
});
