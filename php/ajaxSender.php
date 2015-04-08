<?php
require __DIR__ . '/vendor/autoload.php';
require_once("PHP-MPD-Client-develop/mpd/MPD.php");

use PHPMPDClient\MPD AS mpd;

class AjaxAnswer implements JsonSerializable
{
  public $infoText;
  public $result;
  public $state; //0 ok, 1 error
  function __construct($text="", $result="", $state = 0)
  {
    $this->infoText = $text;
    $this->result = $result;
    $this->state = $state; //state to 1 means error
  }
  // function called when encoded with json_encode
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}

class AjaxSender
{
  private $action = NULL;
  private $dbName = "../data/RadioMPD.sqlite";
  private $playList = "stations";
    
  function __construct() {
    $this->action = $_GET['action'];
    //print "Konstruktor with " .  $this->action . " and type " . gettype($this->action);
  } 

    
  //returns handle for db, if it does not exist create db  
  private function getDBAccess()
  {
    //$db = sqlite_open("sender.sqlite"); wohl alter stil :-), bin wohl zu lang raus, also pdo
    try  
    {
      $result = new PDO("sqlite:" . $this->dbName);
    }
    catch (PDOException $e)
    {
      $result = $e->getMessage(); //real error, can't solve
      $result = "I think we have no write access to " .  $this->dbName.", you have to solve it<br>" . $result ; 
      throw new Exception($result);
    }
    //create table(s) if they does not exist
    $query = "CREATE TABLE IF NOT EXISTS sender (pos INTEGER PRIMARY KEY,  name VARCHAR UNIQUE , url VARCHAR , additionalUrl VARCHAR)";
    //es far as I understand the type is only a hint how to store the data see type-affinity in https://www.sqlite.org/
    $result->exec($query); 
    return $result;
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
  //get list of sender as json-encoded string and set it to current playlist in mpd
  //requires running mpd
  private function getStationsFromDB() 
  {
    $db = $this->getDBAccess(); //here not handling exception
    $dbRes = $db->query("SELECT * FROM sender ORDER by pos");
    $result = array();
    $stations = array();
    while($row = $dbRes->fetch(PDO::FETCH_ASSOC)) 
    {
        $result[] = ['pos'=>$row['pos'] , 'name'=>$row['name']
                    ,'url'=>$row['url'], 'additionalUrl' => $row['additionalUrl'] ];
        $stations[] = $row['url']; 
    }
    //$this->tryConnectMpd();
    //mpd::setArray($stations);
    //mpd::disconnect();
    return array($result, $stations);;
  }
  //returns mpd status
  private function getAktuell()
  {
    $this->tryConnectMpd();
    $result = mpd::status();
    mpd::disconnect();
    return $result;
  }
  //returns current song
  private function currentSong()
  {
    $this->tryConnectMpd();
    $result = mpd::currentSong();
    mpd::disconnect();
    return $result;
  }
  //switch station, zero based
  private function switchTo($station)
  {
    $this->tryConnectMpd();
    list($notUsed,$stations)=$this->getStationsFromDB();
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
          case "liste":
              list($result->result, $notUsed) = $this->getStationsFromDB();              
          break;
          case "aktuell":
            $result->result = $this->getAktuell();
          break;
          case "switch":
            $station = $_GET['station'];
            $result->result = $this->switchTo($station-1);
            break;
          case "currentSong":
            $result->result=$this->currentSong();
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

?>