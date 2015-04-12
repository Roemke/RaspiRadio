<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Radio DB Interface</title>
	<meta name="description" content="db Interface">

	<!-- nur jquery selbst-->
	<script src="../js/jquery-2.1.1.min.js" type="text/javascript"></script>
	<!-- notiz (da schon 2 mal vergessen )  sollte man mobileinit verwenden wollen, muss man vor dem
	  Einbinden von jquery mobile an den event binden --> 
	<!-- und jquery mobile js  als letztes --> 
</head>
<script>
$(document).ready(function()
{
  $('#bReload').on('click',function(data)
  {
    refreshFromDB();
  });
  $('#bSave').on('click',function(data)
  {
    saveToDB();
  });

});

function refreshFromDB(data)
{
  //console.log("Answer is " + data);
  $.get("ajaxSender.php", {action: 'liste'}, function(data)
  {
	  var result = $.parseJSON(data);
    console.log(data);
  	$('#tArea').val('');
  	if (result)
  	{
      var text="";
    	for (var i = 0; i < result.result.length; ++i)
     	{
     	  var addUrl = (result.result[i].additionalUrl == null ? '' : result.result[i].additionalUrl);
      	text += result.result[i].name + ',';
				text += result.result[i].url +',';
				text += addUrl +'\n';
     	}
			$('#tArea').val(text);
  	}
	});
}
function saveToDB()
{
  text = $('#tArea').val();
  $.post("ajaxSender.php", {action: 'save', data : text});
}
</script>
<body>
<p>
In the following textarea you can add the entries which you like to store into the db.
</p>
<p>
I have done not much work to the interface cause I normally use 5 to 10 stations.
</p>
<p>
Each row represents an entry, seperated by comma. <br>
Use Name, URL, additional URL (not used in the moment, let it empty) 
</p>
<button type="button" id='bReload'>reload from Db</button>
<button type="button" id='bSave'>save to Db</button>
<textarea id='tArea' rows=15 	  style="width: 1500px; overflow-x:scroll;">
<?php
  require_once("db.php");
  $db = new DBRadio();
  list($stations,$unused) = $db->getStationsFromDB();
  foreach ($stations as $station)
    echo $station['name'] .',' . $station['url'] .',' . $station['additionalUrl'] ."\n"; 
?>
</textarea>
</body>
</html>
