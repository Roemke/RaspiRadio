<?php
/* beim Start des raspberry ausfuehren - auf 1. Sender und vol auf 70 */
require_once('PHP-MPD-Client-develop/mpd/MPD.php');
require_once('db.php');
require_once('AjaxAnswer.php');
require_once('AjaxSenderClass.php');


$sender = new AjaxSender();
$station = isset($_GET['station']) ? $_GET['station'] : -1;
$up = isset($_GET['up']) ? true : false;
$down = isset($_GET['down']) ? true : false;

if ($up || $down)
{
   //actual station, hmm hier laeuft noch was falsch - keine Ahnung
   $res = $sender->getStatus();
   echo "<textarea rows=50 cols=120>";
   echo $res;
   echo "</textarea>";
   //$station = $res->Pos;
   //number of stations
   //$db = new DBRadio();
   //list($stations,$notUsed)=$db->getStationsFromDB();
   //$anzahl = count($stations);
   //$station = ($station >= $anzahl) ? 0 : $station;
   //$station = ($station < 0) ? $anzahl -1 : $station;   
}
//$sender->switchTo($station);
//$res = $sender->currentSong();

echo "now on " . $res->Name;
//phpinfo();
//$sender->switchTo(0);
//$sender->showActual();
//$sender->setVolume(85); //85 bei pi ist 70 bei mir - skaliert, da pi recht leise
//echo "hello";
?>