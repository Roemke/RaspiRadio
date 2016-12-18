package main;

use strict;
use warnings;
use HttpUtils;   
use JSON;

sub RASPIO_Initialize($)
{
  #die funktionen, die in fhem bekannt sein sollen 
  my ($hash) = @_; #@_ uebergebene Var als array
  $hash->{DefFn}         = "RASPIO_Define";
  $hash->{UndefFn}       = "RASPIO_UnDef";
  $hash->{DeleteFn}      = "RASPIO_Delete";
  $hash->{UpdateFn}      = "RASPIO_Update";
  $hash->{SetFn}         = "RASPIO_Set";
  $hash->{GetFn}         = "RASPIO_Get";
#  $hash->{AttrFn}        = "RASPIO_Attr"; #vor dem aufruf setzen attribut brauchen wir nicht
#  $hash->{ReadFn}        = "RASPIO_Read"; #falls filedescriptor daten zum lesen hat - erinnere mich an select
                                           #da polling per http - unnoetig (?)
#  $hash->{ReadyFn}       = "RASPIO_Ready";#wiederaufbau einer Verbindung (Linux) ebenfalls unnoetig
#  $hash->{NotifyFn}      = "RASPIO_Notify"; # reaktion auf andere events - koennte hier auf die steckdose des raspberry
                                             #reagieren - ist aber unnoetig
#  $hash->{RenameFn}      = "RASPIO_Rename"; # nach dem umbennen, brauche ich auch nicht
#  $hash->{ShutdownFn}    = "RASPIO_Shutdown"; # bevor fhem gestoppt wird - also auch unnoetig  
} 

#stelle logging auf Level 5, mir wird sonst zuviel geloggt
#empfohlen wird 3 im normalbetrieb

#need name, url, interval 
#url z.B. http://192.168.0.200/radio/php/ajaxSender.php
sub RASPIO_Define($$)
{
  my ( $hash, $def ) = @_;
  my @a = split( "[ \t][ \t]*", $def );
  if (int(@a) >= 3)
  {      
    my $name   	= $a[0];
    my $module 	= $a[1];
    my $url	= $a[2];
    my $inter	= 300;
     
    if(int(@a) == 4) 
    { 
      $inter = $a[3]; 
      if ($inter < 5) {
          return "interval too small, please use something > 5s, default is 300 seconds";
      }
    }
    #speichern
    $hash->{url}	= $url;
    $hash->{inter}	= $inter;        
    $hash->{status} 	= undef;
    
    #InternalTimer(gettimeofday()+2, "RASPIO_Update", $hash);#in 2 sekunden erste abfrage
    InternalTimer(gettimeofday()+2, "RASPIO_statusRequest" , $hash); # und einen einmaligen statusRequest
    #statusRequest ruft auch das update
    $attr{$name}{devStateIcon} =
          'on:rc_GREEN:off off:rc_YELLOW:on absent:rc_STOP:on';
    $attr{$name}{webCmd} = 'station:volume';
                    
  }
  else
  {
    return "to few parameter, need at least Name and url eg define Radio RASPIO 192.168.0.200/radio/";
  }
  return undef; # undef hier wichtig, ansonsten kommt anscheinend manchmal der timestamp zurueck, der wird als
  # fehler interpretiert und angezeigt
}

#aufruf nach dem ungültig werden
#stuertz manchmal ab, liegt nicht am RemoveInternalTimer
#liegt einfach daran dass ich den namen falsch eigegeben habe ... oben 
sub RASPIO_UnDef($$)
{
  my ($hash,$name) = @_;
  RemoveInternalTimer($hash);#falls noch einer existiert, weg  
  return undef;  
}

#aufruf nach undef, muss hier nichts mehr machen
#z.B. erzeugte Daten loeschen oder aehnliches
sub RASPIO_Delete($$)
{
  return undef;
}


#der ajaxSender kennt 
#action=  showActual liste statusAndCurrent status pause play switch
#currentSong volume save completeState
#hier brauchen wir eigentlich nur title, station, volume, playing (true / false)
#und stationList
#brauche ich ueberhaupt ein get? die readings werden sowieso regelmäßig erneuert
#doch die Liste der Stationen brauche ich auf jeden  Fall, nenne Sie stationList
#die gui ruf auf jeden fall get auf um die möglichen Werte abzufragen, daher muss
#die Fehlermeldung stimmen 
sub RASPIO_Get($$@) 
{
	my ( $hash, $name, $opt, @args ) = @_;
 
	return "\"get $name\" needs at least one argument" unless(defined($opt));
 
	if ( $opt =~ /^(state|volume|station|title|stationList)$/ )
  {           
     return ReadingsVal( $name, $opt, "" );
  }
  else
	{
	  return "Unknown argument $opt, choose one of state volume station title stationList";
	}
}

#set der einzelnen Werte, also absenden von http-Befehlen
#bei enigma abgeschaut: statusRequest - kann die Liste der stations neu einlesen
sub RASPIO_Set($$@)
{
  my ( $hash, $name, $cmd, @args ) = @_;
  my $stationList = ReadingsVal($name,"stationList","-");
  return "\"set $name\" needs at least one argument" unless(defined($cmd));
  if ( $cmd  eq "statusRequest" ) 
  {
    RASPIO_statusRequest($hash);
  }
  elsif ($cmd =~ /^(volume|on|off|station)$/)
  {
    RASPIO_setOnRaspio($hash,$cmd,$args[0]);
  }
  else
  {
     return "Unknown argument $cmd, choose one of statusRequest:noArg on:noArg off:noArg volume:slider,0,1,100"
          . " station:" . $stationList;
     #wie bei get - aus der Fehlermeldung wird die gui generiert
  }
  return 0;
}

#eigentliches setzen mit http request
#beim setzen von on / off ist das auslösen eines events noetig
#letzter parameter bei readingsSingleUpdate auf 1
#offenbar wird darueber die gui informiert
#beim volume ist es nicht noetig - da wir auch ueber button set 
#aufgerufen, wahrscheinlich passiert dann ein reload der seite
#beim einbau eines sliders wird das aber wieder noetig sein - nein wird bei set
#weiter ueber den Button gesetzt
sub RASPIO_setOnRaspio($$$)
{
   my ($hash,$command, $val)= @_;
   my $url	= $hash->{url}."?action=";
   my $name = $hash->{NAME};
   my $error = 0;
   Log3 3,$name, "$name: call to setOnRasspio with $command ";

	 my $param =
	   {
        keepalive  => 0, # halte nicht offen 
        timeout    => 5,
        hash       => $hash,          # Muss gesetzt werden, damit die Callback funktion wieder $hash hat
        method     => "GET",          # Lesen von Inhalten
        callback   => sub($$$){ return 0; } #brauche ich nicht? - evtl update? 
     }; 

   if ($command eq "volume")
   {
     $param->{url} = $url . "volume&value=" . (50 + int($val/2));
 		 readingsSingleUpdate($hash, "volume", $val,1);
   }
   elsif ($command eq "on") 
   { 
      $param->{url} = $url . "play";
      readingsSingleUpdate($hash, "state", "on",1);       
   }
   elsif ($command eq "off")
   { 
     $param->{url} = $url . "pause";   
     readingsSingleUpdate($hash, "state","off",1);
   }  #shutdown geht zwar, aber dann strom aus / an um ihn zu wecken
   elsif ($command eq "station")
   {
     my $index = 0;
     my @stationArr = split(",",ReadingsVal($name,"stationList","-"));     
     ++$index until $stationArr[$index] eq $val or $index > $#stationArr;
     # $# ist index letztes Element, nicht anzahl der Elemente
     if (!($index > $#stationArr))
     {
       $param->{url} = $url . "switch&station=" . ($index + 1);
       #beim ajax request habe ich es 1-basiert gemacht - warum dass denn, naja 
       Log3 3,$name, "Url: " . $param->{url};       
       readingsSingleUpdate($hash, "station",$val,1);
     }
     else
     {
       $error = 1;
     }
   }
   else  
   { 
     $error=1;
   }   
   if (!$error)
   {
     HttpUtils_NonblockingGet($param);
   }
    
}
#statusRequest, get list of channels and call update
sub RASPIO_statusRequest($)
{
   my ($hash, $def) = @_;
   my $url	= $hash->{url}."?action=listOnlyStationNames";
   my $name = $hash->{NAME};
   Log3 $name, 3, "$name : statusRequest called with hash $hash, url $url ";

	 my $param =
	   {
        url        => $url,
        keepalive  => 0, # halte nicht offen 
        timeout    => 5,
        hash       => $hash,          # Muss gesetzt werden, damit die Callback funktion wieder $hash hat
        method     => "GET",          # Lesen von Inhalten
        #header     => "agent: TeleHeater/2.2.3\r\nUser-Agent: TeleHeater/2.2.3\r\nAccept: application/json",  # Den Header gemäss abzufragender 
        #header duerfte unnoetig sein
        callback   =>  \&RASPIO_parseHttpResponseChannels                                                             # Diese Funktion soll das Ergebnis 
     }; 
   RemoveInternalTimer($hash);
   HttpUtils_NonblockingGet($param);                                                                                     # Starten der HTTP Ab
}

#antwort auf die Anfrage nach der Kanal-Liste    
sub RASPIO_parseHttpResponseChannels
{
    my ($param, $err, $data) = @_;
    my $hash = $param->{hash};
    my $name = $hash->{NAME};
    if($err ne "")                         # wenn ein Fehler bei der HTTP Abfrage aufgetreten ist
    {
        Log3 $name, 5, "$name: error while requesting ".$param->{url}." - $err";   # Eintrag fürs Log
    }
    elsif($data ne "")                  # wenn die Abfrage erfolgreich war ($data enthält die Ergebnisdaten des HTTP Aufrufes)
    {
        Log3 $name, 5, "$name: url ".$param->{url}." returned: $data";    # Eintrag fürs Log
        my $convertedJSON = from_json($data);
				my $liste = join(",",@{$convertedJSON->{result}});
				$liste =~ s/ /_/g;
				#interessant, nur mit @{ wird das array im hash als solches behandelt
				Log3 $name, 5, "$name: is converted to $liste";				
				readingsSingleUpdate($hash, "stationList", $liste,0);
    }	

  RASPIO_Update($hash);
  #und as update holen, dies schaltet den Timer wieder ein
}
#methods to realize non blocking polling whith HttpUtils
sub RASPIO_Update($)
{
    my ($hash, $def) = @_;
	  #my $hash = @_ ; # was def soll ist mir unklar wird anscheinend vom internal timer mit gegeben 
    # @_ ist ein array der uebergebenen Variablen, 
    my $url = $hash->{url}."?action=completeState"; 
    my $param = 
      {
        url        => $url,
        keepalive  => 1, # halte offen 
        timeout    => 5,
        hash       => $hash,          # Muss gesetzt werden, damit die Callback funktion wieder $hash hat
        method     => "GET",          # Lesen von Inhalten
        #header     => "agent: TeleHeater/2.2.3\r\nUser-Agent: TeleHeater/2.2.3\r\nAccept: application/json",  # Den Header gemäss abzufragender Daten ändern
        #header duerfte unnoetig sein
        callback   =>  \&RASPIO_parseHttpResponse                                                             # Diese Funktion soll das Ergebnis dieser HTTP Anfrage bearbeiten
      }; 
      #Log3 $hash->{NAME} , 3, "update called"; klappt
      HttpUtils_NonblockingGet($param);                                                                                     # Starten der HTTP Abfrage. Es gibt keinen Return-Code. 
}
 
#antwort auf den completeState erhalten (ohne senderliste)
sub RASPIO_parseHttpResponse($)
{
    my ($param, $err, $data) = @_;
    my $hash = $param->{hash};
    my $name = $hash->{NAME};
    if($err ne "")                         # wenn ein Fehler bei der HTTP Abfrage aufgetreten ist
    {
        Log3 $name, 5, "$name: error while requesting ".$param->{url}." - $err";   # Eintrag fürs Log
    }
    elsif($data ne "")                  # wenn die Abfrage erfolgreich war ($data enthält die Ergebnisdaten des HTTP Aufrufes)
    {
        Log3 $name, 5, "$name: url ".$param->{url}." returned: $data";    # Eintrag fürs Log
        my $convertedJSON = from_json($data);
        my $volume = $convertedJSON->{result}[0]{values}{volume};
				$volume = ($volume -50) * 2; # liefert ein mpd auf dem raspio und 50 ist fast 0 
        my $state = $convertedJSON->{result}[0]{values}{state};
        my $station = $convertedJSON->{result}[1]{values}{Name};
        $station =~ s/ /_/g;
        my $title = $convertedJSON->{result}[1]{values}{Title};
        Log3 $name, 5, "$name: Extracted Volume $volume, on Station $station, Playing $title, status is $state";
        #An dieser Stelle die Antwort parsen / verarbeiten mit $data
        #readingsSingleUpdate($hash, "fullResponse", $data);   # Readings erzeugen
        
        readingsBeginUpdate($hash);
        readingsBulkUpdate($hash,'volume',$volume);
        readingsBulkUpdate($hash,'station',$station);
        readingsBulkUpdate($hash,'title',$title);
        $state = ($state eq "play") ? "on" : "off";
        readingsBulkUpdate($hash,'state',$state);
        readingsEndUpdate($hash,1); #auch hier event noetig?    
    }
    InternalTimer(gettimeofday()+$hash->{inter}, "RASPIO_Update", $hash);#naechste Abfrage 
}

#just return 1 or true to require 
1;

=pod
=begin html

<a name="RASPIO"></a>
<h3>RASPIO</h3>
<p>Controlling a small internet-radio based on the raspberry pi.</p>
    <p>
      you can find this small radio on <a href="https://github.com/Roemke/RaspiRadio">
      Github: https://github.com/Roemke/RaspiRadio</a> <br>
      And a little bit more information on <a href="http://zb42.de/cc/raspiRadio.php"
      >this Raspio-Page</a>. 
    </p>    
<p>The module is experimental, cause I'm new to fhem - just defined a ENIGMA and some
  plugs. I use it to see how the basics of modul developement work, and to control my
   radio. I think that nearly nobody else uses &quot;my internet radio&quot; so this module is
    useful for me &ndash; but for anybody else? 

<ul>
  <br>
  <a name="RASPIOdefine"></a>
  <b>Define</b>
  <ul>
    <code>define &lt;name&gt; RASPIO &lt;url&gt; &lt;interval&gt;</code>
    <p>
      url is the url of the raspberry pi with installed internetradio, if you reach the radio 
      with the browser under http://192.168.0.200/radio/ then you have to choose http://192.168.0.200/radio/php/ajaxSender.php 
      <br>
      The intervall is used to poll the state of the radio.
    </p>

  </div><br>

  <a name="SISPMset"></a>
  <b>Set</b> <ul>
   <li>volume: obvious </l>
   <li>on/off: obvious </li>
   <li>station: set the channel. In the radio you can define own channels </li>
   <li>statisRequest: manually get the status from the radio, only with this set or by defining
     a new device you can update the list of available stations / channels 
  </ul><br>
  <a name="SISPMget"></a>
  <b>Get</b> <ul>
      <li>according to the set options</li>
      </ul><br>

  <a name="SISPMattr"></a>
  <b>Attributes</b> - just the given ones, I set in the source
  <ul>
    <li>devStateIcon to on:rc_GREEN:off off:rc_YELLOW:on absent:rc_STOP:on</li>
    <li>webCmd to station:volume</li>
  </ul>
  <br>
</ul>

(C) 2016 by Karsten Roemke.
This program is free software. 

=end html
=cut
