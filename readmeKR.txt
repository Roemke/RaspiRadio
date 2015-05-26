eigene Notizen
-----------------------------------------
Achtung bei git muss man beim checkout ein sudo 
verwenden um die db mit zu bekommen RadioMPD.sqlite gehoert www-data
oder ich muesste die rechte mal aendern 
---------------------------------------
- installation von mpd 0.19 - haette ich mir sparen koennen
- ajax-Variante laeuft halbwegs habe mpd-Zusatzklasse von github installiert
  PHP-MPD-Client-develop.zip und erweitert
------intermezzo-----------
- soll mal ueber websockets laufen um eine Art ObserverPattern zu
  implementieren, angelehnt an  https://subinsb.com/live-group-chat-with-php-jquery-websocket
- obiges nutzt PHP Ratchet Library, daher php5-cli nachinstalliert um mit composer arbeiten 
  zu koennen
  pi@raspberrypi /var/www/radio $ curl -sS https://getcomposer.org/installer | php
  liefert composer.phar
  composer.json angelegt, nur mit basis-Information
  pi@raspberrypi /var/www/radio $ php ./composer.phar install
  -> dauert, ergebnis ist komplex und ein paar warnings sind da
  alles nach php verschoben
- Aufwand ist mir zu hoch, sicherlich eine Interessante Variante, evtl.
  sollte man dann aber eine andere Sprache wählen, z.B. node.js 
  also wieder zurueck auf die reine Ajax-Variante, speichere einen git
  branch developmentWebSocket, falls ich mal details nachlesen moechte
---------------------------
- verzichte auf die Playlist von mpd, er hält das ganze auch nur in Dateien 
  vor, lade einfach bei auswahl eines senders die queue neu, bei vielen
  sicher nicht schnell, fuer ein paar sender dürfte es reichen
-----------------------------------------------------------------
intermezzo: server sent events ausprobiert
fazit: laueft aber behindert irgendwie die ajax-requests - verstanden habe
ich das nicht.
also evtl. doch alles per polling lösen. Diesen Stand mal in
developmentServerSentEvents abgelegt
Die Ajax-Requests wurden sehr langsam, bis zu einer Minute statt 150 ms.
Auch beim zeitweisen Abschalten der ServerSentEvents. Sehr seltsam.
--------------------------------------------------
2015-05-26: off dazu, dazu in sudoers 
www-data ALL=(ALL) NOPASSWD: /sbin/halt

start-script dazu dazu in /var/www/radio/startRadio.sh
-> wartet bis mpd laeuft und ruft danach als www-data startRadio.php
dieses setzt auf Station 0 und Lautstärke auf 70% 

außerdem init-script /etc/init.d/radio als hard link auf 
/var/www/radio/radio, dieses ruft startRadio.sh und ein 
pi@raspberrypi /etc/init.d $ sudo insserv radio
um es als start-script zu etablieren - mal versuchen
-------------------------------------------
 