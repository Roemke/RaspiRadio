<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
//first send header once
/*
  server sent events
  inform every client of a change in the mpd status without polling
  inform every client if one client changes the view
  available views 
  0: not set
  1: display of actual (station title, Volume and info get from station)
  2: display of station list defined in db.php 
*/

require_once('PHP-MPD-Client-develop/mpd/MPD.php');
require_once('db.php');
require_once('AjaxAnswer.php');

use PHPMPDClient\MPD AS mpd;


/*
  Server Sent Events adapted from example on html5 rocks
*/

$startedAt = time();

$db = new DBRadio();
$ip =  $_SERVER['REMOTE_ADDR'];

/**
 * Constructs the SSE data format and flushes that data to the client.
 *
 * @param string $id Timestamp/id of this connection.
 * @param object $msg shoud be sended as msg
 * I don't know if we need id and data, on client side (js)
 * the complete message is parsed but id is not used
 */
function sendMsg( $msg) {
  global $startedAt;
  echo "id: $startedAt" . PHP_EOL;
  echo "data: {\n";
  echo "data: \"msg\": " . json_encode ($msg) . ", \n";
  echo "data: \"id\": $startedAt\n";
  echo "data: }\n";
  echo PHP_EOL;
  ob_flush();
  flush();
} 


do {
  // Cap connections at 10 seconds. The browser will reopen the connection on close
  //60 seconds seems to be no problem on web-server of raspi
  if ((time() - $startedAt) > 60) {
    die();
  }//it seems that the EventSource objects reopens the connection if it is closed from here
  
  $actualPage= $db->getState();
  $myPage = $db->getState($ip);

  if ($myPage != $actualPage) //changed something
  {
    $myPage = $actualPage;
    $db->setState($myPage);  
    $answer = new AjaxAnswer('ok');
    $answer->infoText='sender change';
    $answer->result = $myPage;
    sendMsg($answer);
  }
  //file_put_contents("/tmp/sse.txt","Zeit ".time()." last: $last actual: $actual\n",FILE_APPEND);
  
  sleep(1); //sleep half of a second (usleekp needs microseconds)
  //from original author:
  // If we didn't use a while loop, the browser would essentially do polling
  // every ~3seconds. Using the while, we keep the connection open and only make
  // one request.
} while(true);
?>
