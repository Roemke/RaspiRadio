<?php

require_once "mpd/MPD.php";

use PHPMPDClient\MPD AS mpd;

// instantiate a connection
/*
class test {
	public $val1 = 7;
	public $val8 = 8;
	public $val9 = 9;
	public $vala = 'a';
}
$sample = array(
	1, 2, 3,
	array(4, 5, 6),
	new test(),
	array( 'b', 'c', array('d','e','f'))
);
*/
//Kint::dump(mpd::condense($sample));
//ok, works 
mpd::connect();
mpd::clear(); //does it clear the playlist - it should, and it does

var_dump(mpd::addM3u("/var/www/radio/data/stations2.m3u"));

echo "<br><br>";
var_dump(mpd::status());
echo "<br><br>Playlistinfo:";
$answer = mpd::send('playlistinfo');
echo "<br>";
var_dump($answer);
mpd::disconnect();
?>