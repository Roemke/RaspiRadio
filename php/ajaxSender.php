<?php
require_once('PHP-MPD-Client-develop/mpd/MPD.php');
require_once('db.php');
require_once('AjaxAnswer.php');

use PHPMPDClient\MPD AS mpd;


class AjaxSender
{
  private $action = NULL;
  private $playList = "stations";
  private $db;     
  function __construct() {
    $this->action = $_GET['action']; 
  } 

    
  
  //helper function - result from mpd often has an array named values, 
  //there you find key : value -> split it to receive an object
  private function objectFromMPDValues($values)
  {
    $res = new stdclass();
    foreach ($values as $val)
    {
      list ($key,$value) = split(':',$val,2);
      $key = trim($key);
      $value = trim ($value);
      $res->$key = $value;
    }
    return $res;
  }

  //try to connect to mpd
  private function tryConnectMpd()
  {
    try
    {
      $mpdResult = mpd::connect(); //throws exception if no success        
    }
    catch (Exception $e)
    {
      $msg = "Think, that mpd is not running? - (" . $e->getMessage() .")";
      throw new Exception ($msg);
    }
  }  
  //returns mpd status
  private function getStatus()
  {
    $this->tryConnectMpd();
    $result = mpd::status();
    mpd::disconnect();
    $result['values'] = $this->objectFromMPDValues($result['values']);
    return $result;
  }
  //returns current song
  private function currentSong()
  {
    $this->tryConnectMpd();
    $result = mpd::currentSong();
    mpd::disconnect();
    $result['values'] = $this->objectFromMPDValues($result['values']);
    return $result;
  }
  //switch station, zero based
  private function switchTo($station)
  {
    $db = new DBRadio();
    $this->tryConnectMpd();
    list($notUsed,$stations)=$db->getStationsFromDB();
    mpd::setArray($stations); 
    $result= mpd::play($station);
    mpd::disconnect();
    return $result;
  }
  //--------------------------------
  //action which is requested
  public function evaluateRequest()
  {
    $result = new AjaxAnswer("ok");
    if ($this->action === NULL)
    {
      $result->infoText = "no action requested";
      $result->state = 1;
    }
    else 
    {
      try
      {
        switch ($this->action)
        {
          case "showStations": //show stations clicked
            $db = new DBRadio();
            $result->result = $db->setState(States::Stations); 
          break;
          case "showActual": //show stations clicked
            $db = new DBRadio();
            $result->result = $db->setState(States::Actual); 
          break;
          case "liste":
              $db = new DBRadio();
              list($result->result, $notUsed) = $db->getStationsFromDB();              
          break;
          case "status":
            $result->result = $this->getStatus();
          break;
          case "switch":
            $station = $_GET['station'];
            $result->result = $this->switchTo($station-1);
            break;
          case "currentSong":
            $result->result = $this->currentSong();
            break;
          default:
            $result->infoText = "unkown action requested"; 
          break;
        }//eo switch
      }//eo try
      catch(Exception $e)
      {
        $result->state = 1;
        $result->infoText = $e->getMessage();
      }
    }
    return json_encode($result); 
  }
}

$sender = new AjaxSender();
//phpinfo();
$retVal = $sender->evaluateRequest();
echo $retVal;
//echo "hello";
?>