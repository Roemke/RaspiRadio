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
  $station = 0;
}
else
{
  $sender->switchTo($station);
}
echo "have station $station and up $up and down $down";
//phpinfo();
//$sender->switchTo(0);
//$sender->showActual();
//$sender->setVolume(85); //85 bei pi ist 70 bei mir - skaliert, da pi recht leise
//echo "hello";
?>