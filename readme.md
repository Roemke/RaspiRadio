
RadioMPDWeb
=============
Web Interface fuer den MPD - reduziert auf die Moeglichkeit 
Radio-Sender zu verwalten.

Getestet aktuell auf MPD 0.19, funktioniert wahrscheinlich ab mpd 0.16.

Benoetigt: 
- Webserver (getestet mit lighttpd)
- sqlite DB  
  sudo apt-get install sqlite3  
  sudo apt-get install php5-sqlite 
- laufender mpd

Die Seite nutzt einmal pro sekunde ein Polling, so dass ein Display
am Pi, welches die Seite anzeigt aktualisiert wird.

Versuche mit websockets waren mir zu aufwendig.
Versuch mit Server Sent Events hat dazu geführt, dass die 
Ajax-Requests die ich zum Umschalten verwendet habe, bis zu einer
Minute dauerten - auch ein Abbrechen der Verbindung über die SSE 
brachte nichts - sehr seltsam / unverstanden

Beachten: Rechte auf data/RadioMPD.sqlite muessen so sein, dass
der User unter dem der Webserver laeuft, schreiben darf.

Installation - kopiere alles nach /var/www/radio oder was auch immer
das Verzeichnis des Webservers ist.


