/*
  handle stuff around list of stations     	 
*/

//ajax request to update the list of stations
function stationsActualize()
{
  $('#senderList').html('');
  $.get("php/ajaxSender.php", {action: 'liste'}, function(data)
  {
    console.log("Answer is " + data);
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
        $('#senderList').append('<li><a href="#">'+result.result[i].name + '</a></li>'); 
       }
     }
     $('#senderList').listview('refresh');
    }//eo result
    else
      headerMessage("Don't understand - we have no result from ajax call - you should never be here, report a bug");     
  });	
}

