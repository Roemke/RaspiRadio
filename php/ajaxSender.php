<?php
require_once('PHP-MPD-Client-develop/mpd/MPD.php');
require_once('db.php');
require_once('AjaxSenderClass.php');

use PHPMPDClient\MPD AS mpd;

$sender = new AjaxSender();
//phpinfo();
$retVal = $sender->evaluateRequest();
echo $retVal;
//echo "hello";
?>