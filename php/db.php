<?php

/*
  two tables are required in the sqlite
  sender with pos, name, url, additionalUrl (not used in the moment)
  state with ip (primary) , actual 
  actual is integer with the following meaning
  0: not set
  1: display of actual (station title, Volume and info get from station)
  2: display of station list
  ip is used to store the state which a client has or has he value global
  -> if clients state is different from global state the sse.php script
  can inform the client to change the view
  no, only one row in status, I have stopped experiments with server sent events
*/
abstract class States
{
    const NotSet = 0;
    const Actual = 1;
    const Stations = 2;
}

class DBRadio
{
  //private $dbName = "/var/www/radio/data/RadioMPD.sqlite";
  private $dbName = "/tmp/RadioMPD.sqlite";
  //switch auf tmp wg. ro root dateisystem 
  //returns handle for db  
  private function getDBAccess()
  {
    //$db = sqlite_open("sender.sqlite"); wohl alter stil :-), bin wohl zu lang raus, also pdo
    try  
    {
      $handle = new PDO("sqlite:" . $this->dbName);
    }
    catch (PDOException $e)
    {
      $result = $e->getMessage(); //real error, can't solve
      $result = "I think we have no write access to " .  $this->dbName.", you have to solve it<br>" . $result ; 
      throw new Exception($result);
    }
    /*
    //create table(s) if they does not exist, could be done in interface for adding stations to db
    $query = "CREATE TABLE IF NOT EXISTS sender (pos INTEGER PRIMARY KEY,  name VARCHAR UNIQUE , url VARCHAR , additionalUrl VARCHAR)";
    //as far as I understand the type is only a hint how to store the data see type-affinity in https://www.sqlite.org/
    $handle->exec($query);
    */ 
    /*
    $query = "CREATE TABLE IF NOT EXISTS state (ip VARCHAR PRIMARY KEY, actual INTEGER)";
    $handle->exec($query);
    
    $query = "select Count(*) from state";
    $res = $handle->query($query);
    $numOfRows = $res->fetch(PDO::FETCH_NUM)[0];
    if ($numOfRows < 1)
    {
      $query = "insert into state (ip,actual) values ('global',0)";
      $handle->query($query);
    }
    */
    return $handle; 
  }

  //get list of sender as json-encoded string and set it to current playlist in mpd
  //requires running mpd
  public function getStationsFromDB() 
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
  
  //set actual 
  public function setState($newState)
  {
    $db = $this->getDBAccess();
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: '127.0.0.1' ;
    $db->query("update state set actual=$newState where ip='global'");
    $db->query("update state set actual=$newState where ip='$ip'");
    $db->query("insert or ignore into state (ip,actual) values('$ip',$newState)");
    //file_put_contents("/tmp/bla.txt","insert or ignore into state (ip,actual) values('$ip',$newState)");
  }
  //get actual state
  public function getState($ip='global')
  {
    $db = $this->getDBAccess();
    $dbRes = $db->query('select * from state where ip="'.$ip.'"');
    $row = $dbRes->fetch(PDO::FETCH_ASSOC);
    return $row['actual'] ;
  }
  // 
  public function saveToDb($data)
  {
    $db = $this->getDBAccess();
    $db->query("delete  from sender");
    $data=trim($data);
    $lines = explode("\n",$data);    
    foreach ($lines as $line)
    {
      list ($name, $url, $addUrl) = explode(',',$line);
      $name = trim($name);
      $url = trim($url);
      $addUrl=trim($addUrl); 
      $q = "INSERT into sender (name, url, additionalUrl) values ('$name', '$url','$addUrl')";
      if ($url != '')
        $db->query($q);
    }
  }  
}
?>