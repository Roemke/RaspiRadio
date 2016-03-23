<?php
require_once('PHP-MPD-Client-develop/mpd/MPD.php');
require_once('db.php');
require_once('AjaxAnswer.php');
require_once('AjaxSenderClass.php');


$sender = new AjaxSender();
$station = isset($_GET['station']) ? $_GET['station'] : -1;
$up = isset($_GET['up']) ? true : false;
$down = isset($_GET['down']) ? true : false;
$noop = isset($_GET['noop']) ? true : false;
if ($up || $down || $noop)
{
   $res = $sender->currentSong();
   echo "<textarea rows=10 cols=120>";
   var_dump ($res);
   echo "</textarea><p>";
   $station = intval($res['values']->Pos);
   echo "we have station $station <br>"; 
   if ($up)
      $station++;
   else if ($down)
      $station--;
   echo "and go to station $station <br>";   
   //number of stations
   $db = new DBRadio();
   list($stations,$notUsed)=$db->getStationsFromDB();
   $anzahl = count($stations);
   echo "anzahl: $anzahl";
   $station = ($station >= $anzahl) ? 0 : $station;
   $station = ($station < 0) ? $anzahl -1 : $station;   
   //wait until raspi has new information about station
   if (!$noop)
      $sender->switchTo($station);
   $res = $sender->currentSong()['values'];
   while (! isset($res) || ! isset($res->Title) || ! isset($res->Name))
   {	
      $res = $sender->currentSong()['values'];
      echo "<p>sleep</p>";
      usleep(500000); //halbe sekunde	
   }   
   echo "<p><textarea rows=10 cols=120>";
   var_dump ($res);
   echo "</textarea><p>";
   
}



//$res = $sender->currentSong();

//echo "now on " . $res['values']->Name. "<br>";
//phpinfo();
//$sender->switchTo(0);
//$sender->showActual();
//$sender->setVolume(85); //85 bei pi ist 70 bei mir - skaliert, da pi recht leise
//echo "hello";
?>